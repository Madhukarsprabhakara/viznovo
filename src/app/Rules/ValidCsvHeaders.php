<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

class ValidCsvHeaders implements ValidationRule
{
    /**
     * @return array<string, string>
     */
    private function getReservedHeaderAliases(): array
    {
        return [
            'id' => 'id',
            'created_at' => 'created_at_ts',
            'created_at_ts' => 'created_at_ts',
            'updated_at' => 'updated_at_ts',
            'updated_at_ts' => 'updated_at_ts',
        ];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail("The {$attribute} must be a file.");
            return;
        }

        if (!$value->isValid()) {
            $fail("The {$attribute} failed to upload.");
            return;
        }

        $clientExtension = strtolower(trim((string) $value->getClientOriginalExtension()));
        if ($clientExtension !== 'csv') {
            // Mixed-upload endpoint: only enforce CSV header checks for .csv files.
            return;
        }

        $path = $value->getRealPath();
        if (!is_string($path) || $path === '' || !is_file($path)) {
            $fail("The {$attribute} must be a valid CSV file.");
            return;
        }

        if ($this->looksLikeUnsupportedBinaryForCsv($path)) {
            $fail("The {$attribute} must be a CSV file.");
            return;
        }

        $headers = $this->readCsvHeaders($path);
        if ($headers === null || count($headers) === 0) {
            $fail("The {$attribute} must be a valid CSV file with a header row.");
            return;
        }

        [$emptyHeaderColumns, $duplicateHeaders, $reservedHeaders] = $this->analyzeHeaders($headers);

        if (count($emptyHeaderColumns) === 0 && count($duplicateHeaders) === 0 && count($reservedHeaders) === 0) {
            return;
        }

        $issues = [];

        if (count($emptyHeaderColumns) > 0) {
            $issues[] = 'empty column headers at columns: ' . implode(', ', $emptyHeaderColumns);
        }

        if (count($duplicateHeaders) > 0) {
            $issues[] = 'duplicate column names: ' . implode(', ', $duplicateHeaders);
        }

        if (count($reservedHeaders) > 0) {
            $reservedHeaderMessages = [];

            foreach ($reservedHeaders as $reservedHeader => $columns) {
                $reservedHeaderMessages[] = '"' . $reservedHeader . '" at columns: ' . implode(', ', $columns);
            }

            $issues[] = 'reserved column names found: ' . implode(', ', $reservedHeaderMessages) . '. These headers are system-managed. Rename or remove them';
        }

        $fail('The ' . $attribute . ' has an invalid CSV header row (' . implode('; ', $issues) . ').');
    }

    private function looksLikeUnsupportedBinaryForCsv(string $path): bool
    {
        $prefix = @file_get_contents($path, false, null, 0, 4096);
        if ($prefix === false) {
            return true;
        }

        $prefix = $this->stripUtf8Bom($prefix);

        // Common signatures for file types that are not CSV.
        if (str_starts_with($prefix, '%PDF-')) {
            return true;
        }

        if (str_starts_with($prefix, "PK\x03\x04")) {
            return true;
        }

        if (str_starts_with($prefix, "\x89PNG\r\n\x1A\n")) {
            return true;
        }

        if (str_starts_with($prefix, "GIF87a") || str_starts_with($prefix, "GIF89a")) {
            return true;
        }

        if (str_starts_with($prefix, "\xFF\xD8\xFF")) {
            return true;
        }

        // Heuristic: NUL bytes usually indicate a binary file, not a CSV.
        return str_contains($prefix, "\0");
    }

    private function readCsvHeaders(string $path): ?array
    {
        try {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);

            $headers = $csv->getHeader();
        } catch (\Throwable) {
            return null;
        }

        return is_array($headers) ? $headers : null;
    }

    private function analyzeHeaders(array $headers): array
    {
        $emptyHeaderColumns = [];
        $normalizedHeaders = [];
        $reservedHeaders = [];
        $reservedHeaderAliases = $this->getReservedHeaderAliases();

        foreach (array_values($headers) as $index => $header) {
            $cell = (string) $header;

            if ($index === 0) {
                $cell = $this->stripUtf8Bom($cell);
            }

            $cell = trim($cell);
            $normalized = $this->lower($cell);
            $normalizedIdentifier = $this->normalizeHeaderIdentifier($cell);

            if ($normalized === '') {
                $emptyHeaderColumns[] = $index + 1;
            }

            $reservedHeader = $reservedHeaderAliases[$normalized] ?? $reservedHeaderAliases[$normalizedIdentifier] ?? null;
            if ($reservedHeader !== null) {
                $reservedHeaders[$reservedHeader][] = $index + 1;
            }

            $normalizedHeaders[] = $normalized;
        }

        return [$emptyHeaderColumns, $this->findDuplicates($normalizedHeaders), $reservedHeaders];
    }

    private function stripUtf8Bom(string $value): string
    {
        if (strncmp($value, "\xEF\xBB\xBF", 3) === 0) {
            return substr($value, 3);
        }

        return $value;
    }

    private function lower(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }

    private function normalizeHeaderIdentifier(string $value): string
    {
        $value = trim($value);
        $value = str_replace(' ', '_', $value);
        $value = $this->lower($value);
        $value = preg_replace('/[^a-z0-9_]/', '_', $value) ?? '';
        $value = preg_replace('/_+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    private function findDuplicates(array $normalizedHeaders): array
    {
        $counts = [];

        foreach ($normalizedHeaders as $header) {
            if ($header === '') {
                continue;
            }

            $counts[$header] = ($counts[$header] ?? 0) + 1;
        }

        $duplicates = [];
        foreach ($counts as $header => $count) {
            if ($count > 1) {
                $duplicates[] = (string) $header;
            }
        }

        sort($duplicates);

        return $duplicates;
    }
}

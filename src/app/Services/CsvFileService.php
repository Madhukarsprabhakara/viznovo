<?php

namespace App\Services;

use App\Models\ProjectData;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;
class CsvFileService
{
    public function getTextTableNameFromCsvName($csvFileNameOrFile, $projectDataId): string
    {
        $originalName = $this->extractCsvBaseName($csvFileNameOrFile);

        $tableBase = $this->sanitizeTableIdentifier($originalName);
        if ($tableBase === '') {
            $tableBase = 'csv';
        }

        if (preg_match('/^[0-9]/', $tableBase) === 1) {
            $tableBase = 't_' . $tableBase;
        }

        $suffix = '_text_' . (string) $projectDataId;
        $maxLength = 55;

        if (strlen($suffix) >= $maxLength) {
            $fallback = 't' . $suffix;
            $fallback = substr($fallback, 0, $maxLength);
            if (preg_match('/^[0-9]/', $fallback) === 1) {
                $fallback = 't_' . $fallback;
                $fallback = substr($fallback, 0, $maxLength);
            }
            return $fallback;
        }

        $baseMaxLength = $maxLength - strlen($suffix);
        $tableBase = substr($tableBase, 0, $baseMaxLength);
        $tableBase = rtrim($tableBase, '_');
        if ($tableBase === '') {
            $tableBase = 'csv';
        }
        if (preg_match('/^[0-9]/', $tableBase) === 1) {
            $tableBase = 't_' . $tableBase;
            $tableBase = substr($tableBase, 0, $baseMaxLength);
            $tableBase = rtrim($tableBase, '_');
        }

        return $tableBase . $suffix;
    }

    private function extractCsvBaseName($csvFileNameOrFile): string
    {
        if ($csvFileNameOrFile instanceof UploadedFile) {
            $name = $csvFileNameOrFile->getClientOriginalName();
        } elseif (is_object($csvFileNameOrFile) && method_exists($csvFileNameOrFile, 'getClientOriginalName')) {
            $name = $csvFileNameOrFile->getClientOriginalName();
        } else {
            $name = (string) $csvFileNameOrFile;
        }

        $base = pathinfo($name, PATHINFO_FILENAME);
        return (string) $base;
    }

    private function sanitizeTableIdentifier(string $name): string
    {
        $name = trim($name);
        $name = str_replace(' ', '_', $name);
        $name = preg_replace('/[^A-Za-z0-9_]/', '_', $name) ?? '';
        $name = preg_replace('/_+/', '_', $name) ?? '';
        $name = trim($name, '_');
        $name = strtolower($name);

        return $name;
    }
    public function getCsvTextTableColumns($csvFilePath = null): array
    {
        $resolvedPath = $this->resolveCsvPath($csvFilePath);
        if ($resolvedPath === null) {
            return [];
        }

        try {
            $csv = Reader::createFromPath($resolvedPath, 'r');
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
        } catch (\Throwable $e) {
            return [];
        }

        $columns = [];
        $seen = [];
        foreach ($headers as $index => $header) {
            $column = $this->sanitizeColumnIdentifier((string) $header, (int) $index);
            $column = $this->makeUniqueColumnIdentifier($column, $seen);
            $columns[] = [
                'csv_header' => (string) $header,
                'db_column' => $column,
            ];
        }

        return $columns;
    }

    private function resolveCsvPath($csvFilePath): ?string
    {
        if ($csvFilePath === null) {
            return null;
        }

        if ($csvFilePath instanceof UploadedFile) {
            $real = $csvFilePath->getRealPath();
            return $real !== false ? $real : null;
        }

        if (is_object($csvFilePath) && method_exists($csvFilePath, 'getRealPath')) {
            $real = $csvFilePath->getRealPath();
            return $real !== false ? $real : null;
        }

        $path = (string) $csvFilePath;
        if ($path === '') {
            return null;
        }

        if (is_file($path)) {
            return $path;
        }

        $privatePath = storage_path('app/private/' . ltrim($path, '/'));
        if (is_file($privatePath)) {
            return $privatePath;
        }

        $defaultPath = storage_path('app/' . ltrim($path, '/'));
        if (is_file($defaultPath)) {
            return $defaultPath;
        }

        return null;
    }

    private function sanitizeColumnIdentifier(string $name, int $index): string
    {
        $maxLength = 55;

        $name = trim($name);
        $name = str_replace(' ', '_', $name);
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_]/', '_', $name) ?? '';
        $name = preg_replace('/_+/', '_', $name) ?? '';
        $name = trim($name, '_');

        if ($name === '') {
            $name = 'col_' . ($index + 1);
        }

        if (preg_match('/^[0-9]/', $name) === 1) {
            $name = 'c_' . $name;
        }

        if (strlen($name) > $maxLength) {
            $name = substr($name, 0, $maxLength);
            $name = rtrim($name, '_');
            if ($name === '') {
                $name = 'col_' . ($index + 1);
            }
            if (preg_match('/^[0-9]/', $name) === 1) {
                $name = 'c_' . $name;
                $name = substr($name, 0, $maxLength);
                $name = rtrim($name, '_');
            }
        }

        return $name;
    }

    private function makeUniqueColumnIdentifier(string $base, array &$seen): string
    {
        $maxLength = 55;

        $candidate = $base;
        $suffixNumber = 1;
        while (isset($seen[$candidate])) {
            $suffixNumber++;
            $suffix = '_' . $suffixNumber;

            $baseMax = $maxLength - strlen($suffix);
            $truncatedBase = substr($base, 0, $baseMax);
            $truncatedBase = rtrim($truncatedBase, '_');
            if ($truncatedBase === '') {
                $truncatedBase = 'col';
            }
            if (preg_match('/^[0-9]/', $truncatedBase) === 1) {
                $truncatedBase = 'c_' . $truncatedBase;
                $truncatedBase = substr($truncatedBase, 0, $baseMax);
                $truncatedBase = rtrim($truncatedBase, '_');
            }

            $candidate = $truncatedBase . $suffix;
        }

        $seen[$candidate] = true;
        return $candidate;
    }
    public function getCsvDataTypeTableColumns(ProjectData $projectData)
    {
        //get 20 records from csv text table
        //get the all data types from csv_data_types table for those columns
        //send it to openai/gemini for data type inference
        //get the data types for each column in a specific json format and store it in project_data_csvs table with type csv_data_type and db_column as column name and csv_header as original csv header name

    }
    
    
}
<?php

namespace App\Services;


class JsonDataService
{


   public function decodeAiJson(string $rawText): array
    {
        $trimmed = trim($rawText);

        $candidates = [];
        $seen = [];
        $addCandidate = function (mixed $value) use (&$candidates, &$seen): void {
            $value = trim((string) $value);
            if ($value === '') {
                return;
            }
            if (isset($seen[$value])) {
                return;
            }
            $seen[$value] = true;
            $candidates[] = $value;
        };

        $addCandidate($trimmed);

        // Strip common code fences.
        $noFences = preg_replace('/^\s*```(?:json)?\s*/i', '', $trimmed);
        $noFences = preg_replace('/\s*```\s*$/', '', (string) $noFences);
        $noFences = trim((string) $noFences);
        $addCandidate($noFences);

        // Sometimes the whole payload is wrapped in quotes.
        foreach ([$trimmed, $noFences] as $v) {
            $v = trim((string) $v);
            if (strlen($v) >= 2 && ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'")))) {
                $addCandidate(substr($v, 1, -1));
            }
        }

        // Extract a likely JSON object/array from mixed content (e.g. trailing error text).
        foreach ([$trimmed, $noFences] as $v) {
            $v = (string) $v;

            $objStart = strpos($v, '{');
            $objEnd = strrpos($v, '}');
            if ($objStart !== false && $objEnd !== false && $objEnd > $objStart) {
                $addCandidate(substr($v, $objStart, $objEnd - $objStart + 1));
            }

            $arrStart = strpos($v, '[');
            $arrEnd = strrpos($v, ']');
            if ($arrStart !== false && $arrEnd !== false && $arrEnd > $arrStart) {
                $addCandidate(substr($v, $arrStart, $arrEnd - $arrStart + 1));
            }
        }

        // Attempt to repair the common invalid pattern: [{""prompt_response"":""...""}]
        foreach ($candidates as $candidate) {
            if (str_contains($candidate, '""')) {
                $addCandidate(str_replace('""', '"', $candidate));
            }
        }

        $lastError = null;

        // Try decoding each candidate.
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }

            $decoded = json_decode($candidate, true);
            $error = json_last_error();
            $errorMessage = json_last_error_msg();
            $lastError = $errorMessage;

            if ($error !== JSON_ERROR_NONE) {
                continue;
            }

            // Some providers / gateways may return JSON as a quoted string (double-encoded).
            if (is_string($decoded)) {
                $decoded2 = json_decode($decoded, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [$decoded2, null];
                }
            }

            // Sometimes JSON is returned as a list of JSON strings: ["{...}"]
            if (is_array($decoded) && array_is_list($decoded)) {
                foreach ($decoded as $item) {
                    if (!is_string($item)) {
                        continue;
                    }
                    $itemDecoded = json_decode($item, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return [$itemDecoded, null];
                    }
                }
            }

            return [$decoded, null];
        }

        // Last error message based on raw trimmed input.
        json_decode($trimmed, true);
        return [null, $lastError ?: (json_last_error_msg() ?: 'Invalid JSON')];
    }
    
}



<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetReader
{
    public static function read(string $fullPath, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'csv' || $extension === 'txt') {
            return self::readCsv($fullPath);
        }

        return self::readExcel($fullPath);
    }

    private static function readCsv(string $fullPath): array
    {
        $raw = file_get_contents($fullPath);
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        $lines = preg_split("/\r\n|\n|\r/", $raw);
        $lines = array_values(array_filter($lines, fn ($l) => $l !== ''));

        if (empty($lines)) {
            return [[], []];
        }

        $firstLine = $lines[0];
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $headerRaw = str_getcsv($firstLine, $delimiter);
        $headers = array_map([self::class, 'normalizeHeader'], $headerRaw);

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $cols = str_getcsv($lines[$i], $delimiter);
            $row = [];
            foreach ($headers as $idx => $key) {
                $row[$key] = $cols[$idx] ?? null;
            }
            $rows[] = $row;
        }

        return [$headers, $rows];
    }

    private static function readExcel(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, false);

        if (empty($data)) {
            return [[], []];
        }

        $headerRaw = array_shift($data);
        $headers = array_map([self::class, 'normalizeHeader'], $headerRaw);

        $rows = [];
        foreach ($data as $line) {
            if (count(array_filter($line, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }
            $row = [];
            foreach ($headers as $idx => $key) {
                $row[$key] = $line[$idx] ?? null;
            }
            $rows[] = $row;
        }

        return [$headers, $rows];
    }

    public static function normalizeHeader(?string $header): string
    {
        $header = trim((string) $header);
        $header = strtolower($header);
        $header = preg_replace('/\s+/', '_', $header);

        return $header;
    }

    public static function toCleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // Notasi ilmiah dengan koma sebagai desimal (locale Indonesia), misal 1,61001E+15
        $normalized = str_replace(',', '.', $value);

        if (preg_match('/^-?\d+(\.\d+)?E[+-]?\d+$/i', $normalized)) {
            $float = (float) $normalized;
            return sprintf('%.0f', $float);
        }

        if (is_numeric($value) && str_contains($value, '.') === false && strpos($value, 'E') === false) {
            return $value;
        }

        // Hapus desimal ,00 atau .00 yang tidak perlu (misal: "1610010004000100,00")
        if (preg_match('/^\d+[,\.]00$/', $value)) {
            return preg_replace('/[,\.]00$/', '', $value);
        }

        return $value;
    }

    public static function toInt(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = str_replace(['.', ','], ['', '.'], (string) $value);
        $value = preg_replace('/[^0-9\-]/', '', (string) $value);

        return (int) $value;
    }
}
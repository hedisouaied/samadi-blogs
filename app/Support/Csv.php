<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Safe CSV generation.
 *
 * Fields are escaped to protect against spreadsheet formula injection
 * (a leading =, +, -, @, tab or carriage return is neutralised).
 */
class Csv
{
    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, array_map([self::class, 'escape'], $headers));

            foreach ($rows as $row) {
                fputcsv($handle, array_map([self::class, 'escape'], $row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function escape(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            $value = "'".$value;
        }

        return $value;
    }
}

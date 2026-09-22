<?php

/**
 * Kirim data tabular sebagai file CSV download.
 * $rows: array asosiatif (baris pertama menentukan header kolom).
 */
function exportCsv(string $filename, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    if (!empty($rows)) {
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

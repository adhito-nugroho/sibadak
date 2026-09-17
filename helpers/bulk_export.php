<?php

declare(strict_types=1);

/**
 * Shared helpers for table bulk actions (export Excel, parse IDs).
 */

/**
 * @param mixed $raw
 * @return list<int>
 */
function bulk_parse_ids(mixed $raw): array
{
    if (!is_array($raw)) {
        return [];
    }

    return array_values(array_unique(array_filter(
        array_map(static fn ($v): int => (int) $v, $raw),
        static fn (int $id): bool => $id > 0
    )));
}

/**
 * @param list<int> $ids
 * @return array{sql: string, params: list<int>}
 */
function bulk_in_clause(array $ids): array
{
    $ids = bulk_parse_ids($ids);
    if ($ids === []) {
        return ['sql' => '0', 'params' => []];
    }

    return [
        'sql' => implode(',', array_fill(0, count($ids), '?')),
        'params' => $ids,
    ];
}

function bulk_flash_redirect(string $path, string $type, string $message): never
{
    set_flash($type, $message);
    header('Location: ' . APP_URL . $path);
    exit;
}

/**
 * Validate POST bulk ids; redirects on failure.
 *
 * @return list<int>
 */
function bulk_require_ids(string $redirectPath, string $emptyLabel = 'item'): array
{
    $ids = bulk_parse_ids($_POST['ids'] ?? []);
    if ($ids === []) {
        bulk_flash_redirect($redirectPath, 'error', 'Pilih minimal satu ' . $emptyLabel . '.');
    }
    if (count($ids) > 500) {
        bulk_flash_redirect($redirectPath, 'error', 'Maksimal 500 item per aksi massal.');
    }

    return $ids;
}

/**
 * Stream a simple XLSX download and exit.
 *
 * @param list<string> $headers Column titles (row 1)
 * @param list<list<scalar|null>> $dataRows Data cells starting at row 2
 */
function bulk_stream_xlsx(
    string $sheetTitle,
    array $headers,
    array $dataRows,
    string $filenamePrefix,
    ?\PDO $pdo = null,
    ?string $modul = null
): never {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $safeTitle = mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $sheetTitle) ?: 'Export', 0, 31);
    $sheet->setTitle($safeTitle);

    $colCount = count($headers);
    for ($c = 0; $c < $colCount; $c++) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1);
        $sheet->setCellValue($col . '1', $headers[$c]);
    }
    if ($colCount > 0) {
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
    }

    $rowNum = 2;
    foreach ($dataRows as $cells) {
        for ($c = 0; $c < $colCount; $c++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1);
            $sheet->setCellValue($col . $rowNum, $cells[$c] ?? '');
        }
        $rowNum++;
    }

    for ($c = 1; $c <= $colCount; $c++) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filenamePrefix) . '-' . date('Ymd-His') . '.xlsx';

    if ($pdo !== null && $modul !== null) {
        log_activity($pdo, $modul, 'bulk_export', 'Export Excel ' . count($dataRows) . ' baris');
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

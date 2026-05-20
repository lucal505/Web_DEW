<?php
namespace App\Services;

class ExportService {
    public function generateCsv(array $data, string $fileName = 'raport.csv'): void {
        // headers for file download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        
        $data = array_map(fn($row) => $row->jsonSerialize(), $data);
        $output = fopen('php://output', 'w');
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0])); // headers
        }
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function generateHtml(array $data, string $fileName = 'raport.html'): void {
        // headers for file download
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        echo "<!DOCTYPE html><html><head><title>Export</title><style>table {border-collapse: collapse;} th, td {border: 1px solid black; padding: 5px;}</style></head><body><table><thead><tr>";
        if (!empty($data)) {
            foreach (array_keys($data[0]) as $column) echo "<th>$column</th>";
        }
        echo "</tr></thead><tbody>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) echo "<td>$cell</td>";
            echo "</tr>";
        }
        echo "</tbody></table></body></html>";
        exit;
    }
}
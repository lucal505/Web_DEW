<?php

namespace App\Importers;

use App\Config\Database;
use App\Interfaces\ImporterInterface;
use PDO;

class SeizureImporter implements ImporterInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function import(string $filePath, int $year): int
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            error_log("[ERROR]: Could not open $filePath.\n");
            return 0;
        }
        $insertionsCount = 0;
        $rowCount = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // convertest fiecare celula la UTF-8
            $data = array_map(fn($cell) => mb_convert_encoding($cell, 'UTF-8', 'auto'), $data);

            // skip empty rows
            $rowCount++;

            if ($rowCount <= 2) {
                continue;
            }

            $drugName = trim($data[0]);
            if (empty($drugName)) {
                error_log("[WARN]: Skipping row $rowCount -> drug name is empty<br>");
                continue;
            }

            $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO drugs (name) VALUES (:name)");
            $stmt->execute(['name' => $drugName]);

            if ($stmt->rowCount() > 0) {
                $insertionsCount++;
            }

            $stmt = $this->pdo->prepare("SELECT id FROM drugs WHERE name = :name");
            $stmt->execute(['name' => $drugName]);
            $drugId = $stmt->fetchColumn();

            // validate negative values
            $grams = isset($data[1]) && is_numeric($data[1]) && (float)$data[1] >= 0 
                ? (float)$data[1] 
                : null;
            $tablets = isset($data[2]) && is_numeric($data[2]) && (int)$data[2] >= 0 
                ? (int)$data[2] 
                : null;
            $doses_units = isset($data[3]) && is_numeric($data[3]) && (int)$data[3] >= 0 
                ? (int)$data[3] 
                : null;
            $milliliters = isset($data[4]) && is_numeric($data[4]) && (float)$data[4] >= 0 
                ? (float)$data[4] 
                : null;
            $seizures_count = isset($data[5]) && is_numeric($data[5]) && (int)$data[5] >= 0 
                ? (int)$data[5] 
                : null;

            $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO drug_seizures
                    (year, drug_id, grams, tablets, doses_units, milliliters, seizures_count) 
                    VALUES (:year, :drug_id, :grams, :tablets, :doses_units, :milliliters, :seizures_count)
                ");
            $stmt->execute([
                'year' => $year,
                'drug_id' => $drugId,
                'grams' => $grams,
                'tablets' => $tablets,
                'doses_units' => $doses_units,
                'milliliters' => $milliliters,
                'seizures_count' => $seizures_count
            ]);

            if ($stmt->rowCount() > 0) {
                $insertionsCount++;
            }
        }
        fclose($handle);
        error_log("[INFO]: Imported $insertionsCount records for SEIZURES (year $year).\n");
        return $insertionsCount;
    }
}

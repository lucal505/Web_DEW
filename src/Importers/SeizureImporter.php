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
        $this->pdo = Database::getConnection();
    }

    public function import(string $filePath, int $year): void
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            echo "[error]: Could not open $filePath.<br>";
        } else {
            $insertionsCount = 0;
            $rowCount = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rowCount++;

                if ($rowCount <= 2) {
                    continue;
                }

                $drugName = trim($data[0]);
                if (empty($drugName)) {
                    echo "[warn]: Skipping row $rowCount -> drug name is empty<br>";
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

                $grams = isset($data[1]) && $data[1] !== '' ? (float)$data[1] : null;
                $tablets = isset($data[2]) && $data[2] !== '' ? (int)$data[2] : null;
                $doses_units = isset($data[3]) && $data[3] !== '' ? (int)$data[3] : null;
                $milliliters = isset($data[4]) && $data[4] !== '' ? (float)$data[4] : null;
                $seizures_count = isset($data[5]) && $data[5] !== '' ? (int)$data[5] : null;

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
            echo "[info]: Imported $insertionsCount records for SEIZURES (year $year)<br>";
        }
    }
}

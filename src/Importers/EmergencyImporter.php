<?php

namespace App\Importers;

use App\Config\Database;
use App\Interfaces\ImporterInterface;
use PDO;

class EmergencyImporter implements ImporterInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function import(string $filePath, int $year): void
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            error_log("[ERROR]: Could not open $filePath.<br>");
        } else {
            $insertionsCount = 0;
            $currentCategory = null;
            $drugHeaders = [];

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                // skip empty rows
                if (empty($data) || !isset($data[0])) {
                    continue;
                }

                $firstCell = trim($data[0]);
                $rowText = implode(" ", $data);

                // selectez categoria curenta
                if (stripos($rowText, 'în funcție de') !== false) {
                    if (stripos($rowText, 'sex') !== false) {
                        $currentCategory = 'gender';
                    } elseif (stripos($rowText, 'vârstă') !== false || stripos($rowText, 'varsta') !== false) {
                        $currentCategory = 'age';
                    } elseif (stripos($rowText, 'calea de administrare') !== false) {
                        $currentCategory = 'administration_route';
                    } elseif (stripos($rowText, 'modelul de consum') !== false) {
                        $currentCategory = 'consumption_pattern';
                    } elseif (stripos($rowText, 'diagnosticul') !== false) {
                        $currentCategory = 'diagnosis';
                    } else {
                        $currentCategory = null;
                    }

                    continue;
                }

                // dau skip daca inca nu am detectat o categorie
                if ($currentCategory === null) continue;

                // salvez tipurile de droguri 
                if (empty($firstCell) && isset($data[1]) && trim($data[1]) !== '') {
                    $drugHeaders = [];

                    foreach ($data as $index => $drugName) {
                        if ($index > 0 && trim($drugName) !== '') {
                            $drugHeaders[$index] = trim($drugName);
                        }
                    }

                    // dau skip la header row
                    continue;
                }

                // inserez datele
                if (!empty($firstCell) && !empty($drugHeaders)) {
                    foreach ($drugHeaders as $colIndex => $drugName) {
                        $count = isset($data[$colIndex]) && trim($data[$colIndex]) !== '' ? (int)$data[$colIndex] : 0;

                        if ($count > 0) {
                            $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO medical_emergencies 
                                (year, drug_type, category, value, count) 
                                VALUES (:year, :drug_type, :category, :value, :count)
                            ");

                            $stmt->execute([
                                'year' => $year,
                                'drug_type' => $drugName,
                                'category' => $currentCategory,
                                'value' => $firstCell,
                                'count' => $count
                            ]);

                            if ($stmt->rowCount() > 0) {
                                $insertionsCount++;
                            }
                        }
                    }
                }
            }
            fclose($handle);
            error_log("[INFO]: Imported $insertionsCount records for EMERGIENCIES (year $year).<br>");
        }
    }
}

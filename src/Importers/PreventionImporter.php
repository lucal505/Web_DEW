<?php

namespace App\Importers;

use App\Config\Database;
use App\Interfaces\ImporterInterface;
use PDO;

class PreventionImporter implements ImporterInterface
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
        $currentSection = null;

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // convertest fiecare celula la UTF-8
            $data = array_map(fn($cell) => mb_convert_encoding($cell, 'UTF-8', 'auto'), $data);

            // skip empty rows
            if (empty($data) || !isset($data[0]) || (count($data) === 1 && trim($data[0]) === '')) {
                continue;
            }

            $firstCell = trim($data[0]);
            $rowText = implode(" ", $data);

            // selectez sectiunea curenta
            if (stripos($rowText, 'PROIECTE NAȚIONALE') !== false) {
                $currentSection = 'projects';
                continue;
            } elseif (stripos($rowText, 'CAMPANII NAȚIONALE') !== false) {
                $currentSection = 'campaigns';
                continue;
            } elseif (stripos($rowText, 'ACTIVITĂȚI DE PREVENIRE') !== false) {
                $currentSection = 'activities';
                continue;
            }

            // skip la headere
            if (isset($data[1]) && (stripos($data[1], 'Nr. beneficiari') !== false
                || stripos($data[1], 'Nr. activități') !== false)) {
                continue;
            }

            if ($currentSection === null) continue;

            // proiecte nationale
            if ($currentSection === 'projects' && !empty($firstCell) && isset($data[1])) {
                $count = (int)$data[1];

                // inserez doar daca nu e valoarea 0
                if ($count > 0) {
                    $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO prevention_projects 
                            (year, project_name, beneficiaries_count)
                            VALUES (:year, :name, :count)");
                    $stmt->execute([
                        'year' => $year,
                        'name' => $firstCell,
                        'count' => $count
                    ]);

                    if ($stmt->rowCount() > 0) {
                        $insertionsCount++;
                    }
                }
            }

            // campanii nationale
            elseif ($currentSection === 'campaigns' && !empty($firstCell) && isset($data[1])) {
                $count = (int)$data[1];

                if ($count > 0) {
                    $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO prevention_campaigns 
                            (year, campaign_name, beneficiaries_count) 
                            VALUES (:year, :name, :count)");
                    $stmt->execute([
                        'year' => $year,
                        'name' => $firstCell,
                        'count' => $count
                    ]);

                    if ($stmt->rowCount() > 0) {
                        $insertionsCount++;
                    }
                }
            }

            // activitati 
            elseif ($currentSection === 'activities' && !empty($firstCell) && isset($data[1]) && isset($data[2])) {
                // validez numerele negative
                $activitiesCount = (int)$data[1];
                if ($activitiesCount < 0) continue;

                $beneficiariesText = trim($data[2]);

                // despart textul dupa virgula ca sa extrag tipurile de beneficiari si numarul lor
                // ex: "60 copii, 23 părinți"
                $parts = explode(',', $beneficiariesText);

                foreach ($parts as $part) {
                    $part = trim($part);
                    if (empty($part)) continue;

                    // caut un numar la început, urmat de text
                    if (preg_match('/^(\d+)\s+(.+)$/', $part, $matches)) {
                        // validez numerele negative
                        $count = (int)$matches[1];
                        if ($count < 0) continue;

                        $type = trim($matches[2]);

                        // setez mediul
                        $specificSetting = $firstCell;

                        $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO prevention_activities 
                                (year, setting, activities_count, beneficiaries_count, beneficiary_type) 
                                VALUES (:year, :setting, :activities_count, :beneficiaries_count, :beneficiary_type)");
                        $stmt->execute([
                            'year' => $year,
                            'setting' => $specificSetting,
                            'activities_count' => $activitiesCount,
                            'beneficiaries_count' => $count,
                            'beneficiary_type' => $type
                        ]);

                        if ($stmt->rowCount() > 0) {
                            $insertionsCount++;
                        }
                    }
                }
            }
        }
        fclose($handle);
        error_log("[INFO]: Imported $insertionsCount records for PREVENTION (year $year).\n");
        return $insertionsCount;
    }
}
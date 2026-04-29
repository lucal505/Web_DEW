<?php

namespace App\Importers;

use App\Config\Database;
use App\Interfaces\ImporterInterface;
use PDO;

class CrimeImporter implements ImporterInterface
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
            error_log("[ERROR]: Could not open $filePath.<br>");
            return;
        } else {
            $insertionsCount = 0;
            $currentSection = null;
            $sentenceHeaders = [];

            // for storing intermediate data before insertion
            $crimesGeneral = ['investigated' => 0, 'indicted' => 0, 'convicted' => 0];
            $crimesGroup = ['identified' => 0, 'involved' => 0];

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                // skipping empty rows
                if (empty($data) || (count($data) === 1 && trim($data[0]) === '')) {
                    continue;
                }

                $rowText = implode(" ", $data);

                if (stripos($rowText, 'CERCETATE, TRIMISE') !== false) {
                    $currentSection = 'general';
                    continue;
                } elseif (strpos($rowText, 'ÎNCADRARE JURIDICĂ') !== false) {
                    $currentSection = 'article';
                    continue;
                } elseif (stripos($rowText, 'SEXE') !== false) {
                    $currentSection = 'demographic';
                    continue;
                } elseif (stripos($rowText, 'GRUPĂRILOR INFRACȚIONALE') !== false) {
                    $currentSection = 'group';
                    continue;
                } elseif (stripos($rowText, 'PEDEPSELOR APLICATE') !== false) {
                    $currentSection = 'sentence';
                    continue;
                }

                if ($currentSection === null) continue;

                $firstCell = trim($data[0]);
                if ($currentSection === 'general' && isset($data[1])) {
                    $value = (int)$data[1];
                    if (stripos($firstCell, 'cercetate') !== false) {
                        $crimesGeneral['investigated'] = $value;
                    } elseif (stripos($firstCell, 'trimise') !== false) {
                        $crimesGeneral['indicted'] = $value;
                    } elseif (stripos($firstCell, 'condamnate') !== false) {
                        $crimesGeneral['convicted'] = $value;
                    }
                } elseif ($currentSection === 'article' && !empty($firstCell) && isset($data[1]) && $data[1] !== '') {
                    $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO crimes_article 
                                            (year, legal_article, count) 
                                            VALUES (:year, :article, :count)");
                    $stmt->execute([
                        'year' => $year,
                        'article' => $firstCell,
                        'count' => (int)$data[1]
                    ]);

                    if ($stmt->rowCount() > 0) {
                        $insertionsCount++;
                    }
                } elseif ($currentSection === 'demographic' && !empty($firstCell) && isset($data[1]) && $data[1] !== '') {

                    // Majori (col 1)
                    if (isset($data[1]) && $data[1] !== '') {
                        $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO crimes_demographic 
                                                (year, gender, age_category, count) 
                                                VALUES (:year, :gender, :age_category, :count)");
                        $stmt->execute([
                            'year' => $year,
                            'gender' => $firstCell,
                            'age_category' => 'Majori',
                            'count' => (int)$data[1]
                        ]);

                        if ($stmt->rowCount() > 0) {
                            $insertionsCount++;
                        }
                    }

                    // Minori (col 2)
                    if (isset($data[2]) && $data[2] !== '') {
                        $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO crimes_demographic 
                                                (year, gender, age_category, count)
                                                VALUES (:year, :gender, :age_category, :count)");
                        $stmt->execute([
                            'year' => $year,
                            'gender' => $firstCell,
                            'age_category' => 'Minori',
                            'count' => (int)$data[2]
                        ]);

                        if ($stmt->rowCount() > 0) {
                            $insertionsCount++;
                        }
                    }
                } elseif ($currentSection === 'group' && isset($data[1]) && $data[1] !== '') {
                    if (stripos($firstCell, 'identificate') !== false) {
                        $crimesGroup['identified'] = (int)$data[1];
                    } elseif (stripos($firstCell, 'implicate') !== false) {
                        $crimesGroup['involved'] = (int)$data[1];
                    }
                } elseif ($currentSection === 'sentence' && isset($data[1]) && $data[1] !== '') {
                    if (empty($firstCell) && isset($data[1]) && !empty($data[1])) {
                        foreach ($data as $index => $law) {
                            if ($index > 0 && !empty(trim($law))) {
                                $sentenceHeaders[$index] = trim($law);
                            }
                        }
                        continue;
                    }

                    if (!empty($firstCell) && !empty($sentenceHeaders)) {
                        foreach ($sentenceHeaders as $index => $lawName) {
                            $count = isset($data[$index]) && $data[$index] !== '' ? (int)$data[$index] : 0;

                            if ($count > 0) { // salvez in DB doar daca exista condamnari
                                $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO crimes_sentence 
                                                        (year, sentence_type, law_reference, count) 
                                                        VALUES (:year, :sentence_type, :law_reference, :count)");
                                $stmt->execute([
                                    'year' => $year,
                                    'sentence_type' => $firstCell,
                                    'law_reference' => $lawName,
                                    'count' => $count
                                ]);

                                if ($stmt->rowCount() > 0) {
                                    $insertionsCount++;
                                }
                            }
                        }
                    }
                }
            }
            fclose($handle);

            // inserez datele acumulate pentru sectiunile 'general' si 'group'
            if ($crimesGeneral['investigated'] > 0) {
                $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO crimes_general 
                                        (year, investigated_persons, indicted_persons, convicted_persons) 
                                        VALUES (:year, :investigated_persons, :indicted_persons, :convicted_persons)");
                $stmt->execute([
                    'year' => $year,
                    'investigated_persons' => $crimesGeneral['investigated'],
                    'indicted_persons' => $crimesGeneral['indicted'],
                    'convicted_persons' => $crimesGeneral['convicted']
                ]);

                if ($stmt->rowCount() > 0) {
                    $insertionsCount++;
                }
            }

            if ($crimesGroup['identified'] > 0) {
                $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO crimes_group 
                                        (year, identified_groups, involved_persons) 
                                        VALUES (:year, :identified_groups, :involved_persons)");
                $stmt->execute([
                    'year' => $year,
                    'identified_groups' => $crimesGroup['identified'],
                    'involved_persons' => $crimesGroup['involved']
                ]);

                if ($stmt->rowCount() > 0) {
                    $insertionsCount++;
                }
            }
            error_log("[INFO]: Imported $insertionsCount records for CRIMES (year $year)<br>");
        }
    }
}

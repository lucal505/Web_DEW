<?php

$db_path = __DIR__ . '/../data/drugs_data.db';
$upload_dir = __DIR__ . '/../uploads/';

try {
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // verific ca baza de date exista si are tabelele necesare 
    // (pdo va crea fisierul .db daca nu exista, dar tabelele 
    // vor lipsi daca nu e rulat si migrate.php)
    $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='drugs'");
    if (!$check->fetch()) {
        echo "[warn]: Database tables not found. Running migration script...<br>";
        require_once __DIR__ . '/migrate.php';
    }

    $files = glob($upload_dir . '*.csv');

    foreach ($files as $file) {
        $fileName = basename($file);

        if(preg_match('/(\d{4})/', $fileName, $matches)) {
            $year = (int)$matches[1];
        } else {
            echo "[warn]: Skipping '$fileName' -> could not find year<br>";
            continue;
        }

        switch (true){
            case (strpos($fileName, 'capturi') !== false):
                echo "[info]: Parsing '$fileName' for year $year<br>";
                importSeizures($pdo, $file, $year);
                break;
            case (strpos($fileName, 'infractionalitate') !== false):
                echo "[info]: Parsing '$fileName' for year $year<br>";
                importCrimes($pdo, $file, $year);
                break;
            case (strpos($fileName, 'urgente') !== false):
                echo "[info]: Parsing '$fileName' for year $year<br>";
                importEmergencies($pdo, $file, $year);
                break;
            case (strpos($fileName, 'proiecte') !== false):
                echo "[info]: Parsing '$fileName' for year $year<br>";
                importPrevention($pdo, $file, $year);
                break;
            default: 
                echo "[warn]: Skipping '$fileName' -> unrecognized category<br>";
        }
    }
} catch (PDOException $err) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $err->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    echo "[error]: " . $err->getMessage() . "<br>";
    exit;
}

function importSeizures($pdo, $file, $year) {
    echo "[info]: Importing seizures data for year $year<br>";

    if (($handle = fopen($file, "r")) !== false){
        $insertionsCount = 0;
        $rowCount = 0;

        while (($data=fgetcsv($handle, 1000, ",")) !== false) {
            $rowCount++;

            if ($rowCount <= 2) {
                continue;
            }

            $drugName = trim($data[0]);
            if (empty($drugName)) {
                echo "[warn]: Skipping row $rowCount -> drug name is empty<br>";
                continue;
            }

            $stmt = $pdo->prepare("INSERT OR IGNORE INTO drugs (name) VALUES (:name)");
            $stmt->execute(['name' => $drugName]);

            if ($stmt->rowCount() > 0) {
                $insertionsCount++;
            }

            $stmt = $pdo->prepare("SELECT id FROM drugs WHERE name = :name");
            $stmt->execute(['name' => $drugName]);
            $drugId = $stmt->fetchColumn();

            $grams = isset($data[1]) && $data[1] !== '' ? (float)$data[1] : null;
            $tablets = isset($data[2]) && $data[2] !== '' ? (int)$data[2] : null;
            $doses_units = isset($data[3]) && $data[3] !== '' ? (int)$data[3] : null;
            $milliliters = isset($data[4]) && $data[4] !== '' ? (float)$data[4] : null;
            $seizures_count = isset($data[5]) && $data[5] !== '' ? (int)$data[5] : null;   

            $stmt = $pdo->prepare("INSERT OR IGNORE INTO drug_seizures
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
    } else {
        echo "[error]: Could not open file $file<br>";
    }
}

function importCrimes($pdo, $file, $year) {
    echo "[info]: Importing crimes data for year $year<br>";

    if (($handle = fopen($file, "r")) !== false){
        $insertionsCount = 0;
        $currentSection = null;
        $sentenceHeaders = [];

        // for storing intermediate data before insertion
        $crimesGeneral = ['investigated' => 0, 'indicted' => 0, 'convicted' => 0];
        $crimesGroup = ['identified' => 0, 'involved' => 0];

        while (($data=fgetcsv($handle, 1000, ",")) !== false) {

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
            } elseif (stripos($rowText, 'GRUPĂRILOR INFRACȚIONALE') !== false){
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
                $stmt = $pdo->prepare("INSERT OR IGNORE INTO crimes_article 
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
                    $stmt = $pdo->prepare("INSERT OR IGNORE INTO crimes_demographic 
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
                    $stmt = $pdo->prepare("INSERT OR IGNORE INTO crimes_demographic 
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
                            $stmt = $pdo->prepare("INSERT OR IGNORE INTO crimes_sentence 
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
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO crimes_general 
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
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO crimes_group 
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
        echo "[info]: Imported $insertionsCount records for CRIMES (year $year)<br>";
    } else {
        echo "[error]: Could not open file $file<br>";   
    }
}

function importEmergencies($pdo, $file, $year) {
    echo "[info]: Importing emergencies data for year $year...<br>";

    if (($handle = fopen($file, "r")) !== false) {
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
                } elseif (stripos($rowText, 'calea de administrare') !== false ) {
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
                if (empty($drugHeaders)) {
                    foreach ($data as $index => $drugName) {
                        if ($index > 0 && trim($drugName) !== '') {
                            $drugHeaders[$index] = trim($drugName); 
                        }
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
                        $stmt = $pdo->prepare("INSERT OR IGNORE INTO medical_emergencies 
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
        echo "[info]: Imported $insertionsCount records for EMERGIENCIES (year $year).<br>";
    } else {
        echo "[error]: Could not open file $file<br>";
    }
}

function importPrevention($pdo, $file, $year) {
    echo "[info]: Importing prevention data for year $year...<br>";

    if (($handle = fopen($file, "r")) !== false) {
        $insertionsCount = 0;
        
        $currentSection = null; 

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            
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
                    $stmt = $pdo->prepare("INSERT OR IGNORE INTO prevention_projects 
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
                    $stmt = $pdo->prepare("INSERT OR IGNORE INTO prevention_campaigns 
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
                $activitiesCount = (int)$data[1];
                $beneficiariesText = trim($data[2]); 
                
                // despart textul dupa virgula ca sa extrag tipurile de beneficiari si numarul lor
                // ex: "60 copii, 23 părinți"
                $parts = explode(',', $beneficiariesText);
                
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (empty($part)) continue;
                    
                    // caut un numar la început, urmat de text
                    if (preg_match('/^(\d+)\s+(.+)$/', $part, $matches)) {
                        $count = (int)$matches[1];
                        $type = trim($matches[2]);
                        
                        // setez mediul cu specificarea beneficiarului in paranteza
                        // am constraint UNIQUE(year, setting) 
                        $specificSetting = $firstCell . ' (' . $type . ')';
                        
                        $stmt = $pdo->prepare("INSERT OR IGNORE INTO prevention_activities 
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
        echo "[info]: Imported $insertionsCount records for PREVENTION (year $year).<br>";
    } else {
        echo "[error]: Could not open file $file<br>";
    }
}

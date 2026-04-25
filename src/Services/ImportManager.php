<?php
namespace App\Services;

use App\Importers\CrimeImporter;
use App\Importers\SeizureImporter;
use App\Importers\EmergencyImporter;
use App\Importers\PreventionImporter;

class ImportManager{
    public function processFiles(string $uploadDir): void{
        $files=glob($uploadDir . '*.csv');

        foreach ($files as $file){
            $fileName=basename($file);

            // verific daca fisierele au o secventa de 4 cifre consecutive in titlu
            if (!preg_match('/(\d{4})/', $fileName, $matches)){
                continue;
            }

            $year=(int)$matches[1];

            $importer=$this->getImporter($fileName);
            if ($importer){
                $importer->import($file, $year);
            }
        }
    }

    private function getImporter(string $fileName){
        return match (true) {
            strpos($fileName, 'capturi') !== false => new SeizureImporter(),
            strpos($fileName, 'infractionalitate') !== false => new CrimeImporter(),
            strpos($fileName, 'proiecte') !== false => new PreventionImporter(),
            strpos($fileName, 'urgente') !== false => new EmergencyImporter(),
            default => null,
        };
    }
}
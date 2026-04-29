<?php
namespace App\Services;

use App\Importers\CrimeImporter;
use App\Importers\SeizureImporter;
use App\Importers\EmergencyImporter;
use App\Importers\PreventionImporter;
use RuntimeException;
use InvalidArgumentException;

class ImportManager{
    public function processFile(string $filePath): void{
        if (!is_file($filePath)){
            throw new InvalidArgumentException("Specified file does not exist: $filePath");
        }

        $fileName=basename($filePath);
        if (!preg_match('/(\d{4})/', $fileName, $matches)){
            throw new InvalidArgumentException("File name must contain a 4-digit year (e.g., urgente_2022.csv).");
        }

        $year = (int)$matches[1];

        $importer = $this->getImporter($fileName);
        
        if ($importer){
            $importer->import($filePath, $year);
        } else {
            throw new RuntimeException("File type not recognized for import.");
        }
    }

    // process all files in uploads/ folder
    public function processFolder(string $dirPath): void {
        // extrag doar fisierele CSV
        $files = glob($dirPath . '*.csv');
        
        if (empty($files)) {
            throw new RuntimeException("No CSV files found in directory: $dirPath");
        }

        foreach ($files as $file) {
            try {
                $this->processFile($file);
            } catch (\Exception $e) {
                error_log("[ERROR] Error processing $file: " . $e->getMessage());
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
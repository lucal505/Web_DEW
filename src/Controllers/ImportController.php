<?php

namespace App\Controllers;

use App\Services\ImportService;
use Exception;

class ImportController
{
    private ImportService $importService;
    private string $uploadDir;

    public function __construct(ImportService $importService, string $uploadDir) {
        $this->importService=$importService;
        $this->uploadDir=$uploadDir;
    }

    public function uploadFile(): void
    {
        $file = $_FILES['fileToUpload'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File upload error.']);
            return;
        }

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Only CSV files are allowed.']);
            return;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $originalName) ?: 'upload.' . $extension;
        $targetPath = rtrim($this->uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
            return;
        }

        try {
            $inserted = $this->importService->processFile($targetPath);
            echo json_encode([
                'success'  => true,
                'message'  => 'File uploaded and imported successfully.',
                'inserted' => $inserted,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Import error: ' . $e->getMessage()]);
        }
    }

    public function importAllFiles(): void
    {
        try {
            $inserted = $this->importService->processFolder($this->uploadDir);
            echo json_encode([
                'success'  => true,
                'message'  => 'All files imported successfully.',
                'inserted' => $inserted,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

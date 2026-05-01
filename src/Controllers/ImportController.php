<?php

namespace App\Controllers;

use App\Services\ImportService;
use Exception;

class ImportController
{
    private AuthController $authController;
    private ImportService $importService;
    private string $uploadDir;

    public function __construct(AuthController $authController, ImportService $importService, string $uploadDir) {
        $this->authController=$authController;
        $this->importService=$importService;
        $this->uploadDir=$uploadDir;
    }

    public function uploadFile(): void
    {
        if (!$this->authController->requireAuth()) {
            return;
        }

        $file = $_FILES['fileToUpload'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File upload error.']);
            return;
        }

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'xls', 'xlsx'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Forbidden file type.']);
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
            $this->importService->processFile($targetPath);
            echo json_encode(['success' => true, 'message' => 'File uploaded and imported successfully.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Import error: ' . $e->getMessage()]);
        }
    }

    public function importAllFiles(): void
    {
        if (!$this->authController->requireAuth()) {
            return;
        }

        try {
            $this->importService->processFolder($this->uploadDir);
            echo json_encode(['success' => true, 'message' => 'All files imported successfully.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

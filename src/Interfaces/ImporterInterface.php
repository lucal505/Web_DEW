<?php
namespace App\Interfaces;

// interfata pentru clasele de import CSV
interface ImporterInterface {
    public function import(string $filePath, int $year): void;
}
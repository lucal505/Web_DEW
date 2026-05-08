<?php
namespace App\Controllers;

use Exception;
use InvalidArgumentException;

abstract class BaseController {
    // returneaza optiunile pentru filtrare
    protected function execute(callable $action): void {
        try {
            $data = $action();
            http_response_code(200);
            echo json_encode($data);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'A apărut o eroare neprevăzută.']);
        }
    }
}
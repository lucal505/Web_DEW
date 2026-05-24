<?php

namespace App\Controllers;

use Exception;
use InvalidArgumentException;

abstract class BaseController
{
    // controllerele care fac operatii de admin seteaza authController
    protected ?AuthController $authController = null;

    protected function execute(callable $action): void
    {
        try {
            $result = $action();

            if (isset($result['status']) && isset($result['body'])) {
                http_response_code($result['status']);
                echo json_encode($result['body']);
            } else {
                http_response_code(200);
                echo json_encode($result);
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // verifica ca exista un token valid de admin
    protected function requireAdmin(): bool
    {
        if ($this->authController === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Admin auth not configured.']);
            return false;
        }

        return $this->authController->requireAuth();
    }

    // citeste body-ul request-ului ca JSON, cu fallback pe $_POST
    protected function getRequestData(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return $_POST;
    }

    protected function getIdFromRequest(): ?int
    {
        return isset($_GET['id']) ? (int)$_GET['id'] : null;
    }
}

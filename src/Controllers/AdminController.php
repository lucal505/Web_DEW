<?php

namespace App\Controllers;

use App\Services\AdminService;

class AdminController extends BaseController
{
    private AdminService $service;

    public function __construct(AdminService $service, ?AuthController $authController = null)
    {
        $this->service = $service;
        $this->authController = $authController;
    }

    public function wipeDatabase(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->execute(function () {
            $count = $this->service->wipeAllData();
            return ['status' => 200, 'body' => ['success' => true, 'tables' => $count]];
        });
    }
}

<?php

namespace App\Controllers;

use App\Services\WipeService;

class WipeController extends BaseController
{
    private WipeService $service;

    public function __construct(WipeService $service)
    {
        $this->service = $service;
    }

    public function wipeDatabase(): void
    {
        $this->execute(function () {
            $count = $this->service->wipeAllData();
            return ['status' => 200, 'body' => ['success' => true, 'tables' => $count]];
        });
    }
}

<?php
namespace App\Controllers;

use App\Services\EmergencyService;

class EmergencyController extends BaseController {
    private EmergencyService $service;

    public function __construct(EmergencyService $service){
        $this->service=$service;
    }

    public function getEmergencies(): void{
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getEmergencies($filters));
    }

    public function getOptions(): void {
        $this->execute(fn() => $this->service->getOptions());
    }
}

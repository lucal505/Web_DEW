<?php
namespace App\Controllers;

use App\Services\EmergencyService;
use App\DTOs\EmergencyFilterDTO;

class EmergencyController extends BaseController {
    private EmergencyService $service;

    public function __construct(EmergencyService $service){
        $this->service=$service;
    }

    public function getEmergencies(): void{
        $filterDTO = EmergencyFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getEmergencies($filterDTO));
    }

    public function getOptions(): void {
        $this->execute(fn() => $this->service->getOptions());
    }
}

<?php
namespace App\Controllers;

use App\Services\SeizureService;
use App\DTOs\Seizures\SeizureFilterDTO;

class SeizureController extends BaseController {
    private SeizureService $service;

    public function __construct(SeizureService $service){
        $this->service=$service;
    }

    public function getSeizures(): void{
        $filterDTO = SeizureFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getSeizures($filterDTO));
    }

    public function getOptions(): void {
        $this->execute(fn() => $this->service->getOptions());
    }
}
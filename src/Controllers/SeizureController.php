<?php
namespace App\Controllers;

use App\Services\SeizureService;

class SeizureController extends BaseController {
    private SeizureService $service;

    public function __construct(SeizureService $service){
        $this->service=$service;
    }

    public function getSeizures(): void{
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getSeizures($filters));
    }

    public function getOptions(): void {
        $this->execute(fn() => $this->service->getOptions());
    }
}
<?php
namespace App\DTOs;

abstract class BaseFilterDTO
{
    public function getYear():      ?int { return null; }
    public function getMinYear():   ?int { return null; }
    public function getMaxYear():   ?int { return null; }
    public function getCount():     ?int { return null; }
    public function getMinCount():  ?int { return null; }
    public function getMaxCount():  ?int { return null; }
    public function getPage():      ?int { return null; }

    abstract public function toArray(): array;
}
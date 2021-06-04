<?php


namespace App\Services\impl;


use App\Models\Dossier;
use YaangVu\LaravelBase\Services\impl\BaseService;

class DossierService extends BaseService
{

    function createModel(): void
    {
        $this->model = new Dossier();
    }
}

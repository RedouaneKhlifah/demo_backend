<?php

namespace App\Services;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    protected $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllServices($searchTerm = null, $perPage = 10)
    {
        return $this->repository->getAllWithSearch($searchTerm, $perPage);
    }

    public function createService(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateService(Service $service, array $data)
    {
        return $this->repository->update($service, $data);
    }

    public function deleteService(Service $service)
    {
        return $this->repository->delete($service);
    }
}

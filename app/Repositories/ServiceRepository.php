<?php

namespace App\Repositories;

use App\Models\Service;

class ServiceRepository
{
    protected $model;

    public function __construct(Service $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->newQuery();

        if ($searchTerm) {
            $query->where('designation', 'like', "%{$searchTerm}%");
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Service $service, array $data)
    {
        $service->update($data);
        return $service;
    }

    public function delete(Service $service)
    {
        return $service->delete();
    }
}

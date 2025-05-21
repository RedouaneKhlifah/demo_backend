<?php

namespace App\Repositories;

use App\Models\Partenaire;

class PartenaireRepository
{
    protected $model;

    public function __construct(Partenaire $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10, $skipPagination = false)
    {
        $query = $this->model->newQuery();
    
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }
    
        $query = $query->orderBy('created_at', 'desc');
        
        if ($skipPagination) {
            return $query->get();
        }
        
        return $query->paginate($perPage);
    }

    public function find(Partenaire $partenaire)
    {
        return $partenaire;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Partenaire $partenaire, array $data)
    {
        $partenaire->update($data);
        return $partenaire;
    }

    public function delete(Partenaire $partenaire)
    {
        return $partenaire->delete();
    }
}
<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;


class ClientRepository
{
    protected $model;

    public function __construct(Client $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10, $skipPagination = false)
    {
        $query = $this->model->newQuery();
    
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('company', 'like', "%{$searchTerm}%");
            });
        }
    
        $query = $query->orderBy('created_at', 'desc');
        
        if ($skipPagination) {
            return $query->get();
        }
        
        return $query->paginate($perPage);
    }
    

    public function getAllArchivedWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->newQuery()->onlyTrashed();
    
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('company', 'like', "%{$searchTerm}%");
            });
        }
    
        return $query->orderBy('created_at', 'desc')->paginate($perPage);    
    }

    public function find(Client $client)
    {
        return $client ;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Client $client, array $data)
    {
        $client->update($data);
        return $client;
    }

    public function delete(Client $client)
    {
        return $client->delete();
    }

    public function restore(int $id)
    {
        $client = $this->model->withTrashed()->findOrFail($id);
    
        if (!$client->trashed()) {
            throw new ModelNotFoundException("Client with ID {$id} is not deleted.");
        }
    
        $client->restore();
        return $client;
    }   
    
    public function bulkDelete(array $ids)
    {
        return Client::whereIn('id', $ids)->delete();
    }
}
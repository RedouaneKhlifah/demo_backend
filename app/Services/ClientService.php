<?php

namespace App\Services;

use App\Models\Client;
use App\Repositories\ClientRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;


class ClientService
{
    protected $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllClients($searchTerm = null, $perPage = 10 , $skipPagination = false)
    {
        return $this->repository->getAllWithSearch($searchTerm, $perPage , $skipPagination);
    }

    public function getAllArchivedClients($searchTerm = null, $perPage = 10)
    {
        return $this->repository->getAllArchivedWithSearch($searchTerm, $perPage);
    }

    public function getClient(Client $client) 
    {
        return $this->repository->find($client);
    }

    public function createClient(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateClient(Client $client, array $data)
    {
        return $this->repository->update($client, $data);
    }

    public function restoreClient(int $id)
    {
        return $this->repository->restore($id);
    }

    public function deleteClient(Client $client)
    {
        return $this->repository->delete($client);
    }

    public function deleteClientByIds(array $ids)
    {
         DB::beginTransaction();
 
         try {
             $deleted = $this->repository->bulkDelete($ids);
             DB::commit();
 
             return $deleted;
         } catch (\Exception $e) {
             DB::rollBack();
             throw $e;
         }
     }

}
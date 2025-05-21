<?php

namespace App\Services;

use App\Models\FactureService;
use App\Repositories\FactureServiceRepository;

class FactureServiceService
{
    protected $repository;

    public function __construct(FactureServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllFactureServices($searchTerm = null, $perPage = 10)
    {
        $factureServiceCollection = $this->repository->getAllWithSearch($searchTerm, $perPage);
        // Format the services array for each FactureService
        $factureServiceCollection->getCollection()->transform(function ($factureService) {
            return $this->formatServices($factureService);
        });
        
        return $factureServiceCollection;
    }

    public function getFactureService(FactureService $factureService)
    {
        return $this->repository->find($factureService);
    }

    public function createFactureService(array $data)
    {
        $factureService = $this->repository->create($data);
        return $this->formatServices($factureService);
    }

    public function updateFactureService(FactureService $factureService, array $data)
    {
        $updatedFactureService = $this->repository->update($factureService, $data);
        return $this->formatServices($updatedFactureService);
    }

    public function deleteFactureService(FactureService $factureService)
    {
        return $this->repository->delete($factureService);
    }

    public function formatServices(FactureService $factureService): FactureService
    {
        $formattedServices = $factureService->services->map(function ($service) {
            $serviceArray = $service->toArray();
            $pivotData = $serviceArray['pivot'];

            // Remove the pivot entry
            unset($serviceArray['pivot']);

            // Merge pivot data into the main service array
            return array_merge($serviceArray, $pivotData);
        });

        // Update the services relation in the FactureService model
        $factureService->setRelation('services', $formattedServices);

        return $factureService;
    }
}
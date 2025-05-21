<?php

namespace App\Repositories;

use App\Models\FactureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FactureServiceRepository
{
    protected $model;

    public function __construct(FactureService $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->with(['services', 'client']);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('reference', 'like', "%{$searchTerm}%")
                  ->orWhereHas('client', function ($q) use ($searchTerm) {
                      $q->where('company', 'like', "%{$searchTerm}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(FactureService $factureService)
    {
        return $factureService->load(['services', 'client']);
    }

    public function create(array $data)
    {
        $factureService = $this->model->create($data);
        
        // If services are included in the data, attach them
        if (isset($data['services'])) {
            $services = collect($data['services'])->mapWithKeys(function ($item) {
                return [$item['service_id'] => [
                    'prix' => $item['prix'],
                    'quantity' => $item['quantity']
                ]];
            });
            $factureService->services()->attach($services);
        }

        return $factureService->load(['services', 'client']);
    }

    public function update(FactureService $factureService, array $data)
    {
        // First update the factureService model with basic data
        $factureService->update($data);
    
        // If services are included in the data, sync them
        if (isset($data['services'])) {
            $services = collect($data['services'])->mapWithKeys(function ($item) {
                // Ensure we're not passing factureService_id in the pivot data
                return [$item['service_id'] => [
                    'prix' => $item['prix'],
                    'quantity' => $item['quantity']
                ]];
            });
            
            $factureService->services()->sync($services);
        }
    
        // Reload the model with its relationships
        return $factureService->fresh(['services', 'client']);
    }

    public function delete(FactureService $factureService)
    {
        return $factureService->delete();
    }

    public function getPartialStatusData($startDate, $endDate): array
    {
        $factureServices = FactureService::with('services')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->filter(function ($factureService) {
                return $factureService->paid_amount > 0 && ($factureService->paid_amount < $factureService->totals);
            });

        return [
            'total' => $factureServices->sum('totals'),
            'paid_amount' => $factureServices->sum('paid_amount')
        ];
    }

    public function getPaidPartialCompleteSum($startDate, $endDate): float
    {
        return FactureService::with('services')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->filter(function ($factureService) {
                return $factureService->paid_amount > 0;
            })
            ->sum('paid_amount');
    }

    public function getRevenue($startDate, $endDate): float
    {
        $revenue = FactureService::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->filter(function ($factureService) {
                return $factureService->paid_amount > 0;
            })
            ->sum('totals');
        
        return $revenue;
    }

    public function getFactureServicesForChart($startDate, $endDate): array
    {
        // Get all factureServices between the provided start and end date
        $factureServices = FactureService::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($factureService) {
                return [
                    'date' => $factureService->created_at->format('Y-m-d'), // format the date to YYYY-MM-DD
                    'value' => $factureService->totals, // or any other relevant metric for the chart
                ];
            });

        return $factureServices->toArray(); // Return the result in the required format for the chart
    }
}
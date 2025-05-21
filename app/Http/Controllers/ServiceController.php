<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $services = $this->serviceService->getAllServices($searchTerm, $perPage);
        return response()->json($services);
    }

    public function store(ServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->createService($request->validated());
        return response()->json($service, 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function update(ServiceRequest $request, Service $service): JsonResponse
    {
        $updatedService = $this->serviceService->updateService($service, $request->validated());
        return response()->json($updatedService);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->deleteService($service);
        return response()->json(null, 204);
    }
}

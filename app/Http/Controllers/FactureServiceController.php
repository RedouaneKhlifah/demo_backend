<?php

namespace App\Http\Controllers;

use App\Events\ModelUpdated;
use App\Http\Requests\FactureServiceRequest;
use App\Models\FactureService;
use App\Services\FactureServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FactureServiceController extends Controller
{
    protected $factureServiceService;

    public function __construct(FactureServiceService $factureServiceService)
    {
        $this->factureServiceService = $factureServiceService;
    }

    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $factureServices = $this->factureServiceService->getAllFactureServices($searchTerm, $perPage);
        return response()->json($factureServices);
    }

    public function store(FactureServiceRequest $request): JsonResponse
    {
        $factureService = $this->factureServiceService->createFactureService($request->validated());
        return response()->json($factureService, 201);
    }

    public function show(FactureService $factureService): JsonResponse
    {
        $factureService = $this->factureServiceService->getFactureService($factureService);
        return response()->json($factureService);
    }

    public function update(FactureServiceRequest $request, FactureService $factureService): JsonResponse
    {
        $factureService = $this->factureServiceService->updateFactureService($factureService, $request->validated());
        return $factureService
            ? response()->json($factureService)
            : response()->json(['message' => 'FactureService not found'], 404);
    }

    public function destroy(FactureService $factureService): JsonResponse
    {
        $this->factureServiceService->deleteFactureService($factureService);
        return response()->json(null, 204);
    }

}
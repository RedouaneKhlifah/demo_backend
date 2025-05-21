<?php

namespace App\Http\Controllers;

use App\Events\ModelUpdated;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Number;



class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $skipPagination = $request->query('all', false);
        $clients = $this->clientService->getAllClients($searchTerm, $perPage , $skipPagination);
        return response()->json($clients);
    }

    public function archived(Request $request): JsonResponse
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $clients = $this->clientService->getAllArchivedClients($searchTerm, $perPage);
        return response()->json($clients);
    }


    public function store(ClientRequest $request): JsonResponse
    {
        $client = $this->clientService->createClient($request->validated());
        return response()->json($client, 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client = $this->clientService->getClient($client);
        return response()->json($client);
    }

    public function update(ClientRequest $request, Client $client): JsonResponse
    {
        $client = $this->clientService->updateClient($client, $request->validated());

        return $client
            ? response()->json($client)
            : response()->json(['message' => 'Client not found'], 404);
    }

    public function restore(int $id): JsonResponse
    {
        $client = $this->clientService->restoreClient($id);
        return response()->json($client, 200);
    }
    

    public function destroy(Client $client): JsonResponse
    {
        $success = $this->clientService->deleteClient($client);

        return response()->json(null, 204);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids');
        $success = $this->clientService->deleteClientByIds($ids);

        return response()->json(['success' => $success]);
    }
}
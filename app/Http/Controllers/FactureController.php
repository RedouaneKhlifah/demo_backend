<?php

namespace App\Http\Controllers;

use App\Http\Requests\FactureRequest;
use App\Http\Resources\FactureResource;
use App\Models\Facture;
use App\Services\FactureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class FactureController extends Controller
{
    protected $factureService;


    public function __construct(FactureService $factureService)
    {
        $this->factureService = $factureService;
    }

    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $facture = $this->factureService->getAllFacture($searchTerm, $perPage);
        return FactureResource::collection($facture);
    }

    // public function store(FactureRequest $request): JsonResponse
    // {
    //     $facture = $this->factureService->createFacture($request->validated());

    //     return response()->json($facture, 201);
    // }

    public function show(Facture $facture)
    {
        $facture = $this->factureService->getFacture($facture);
        return new FactureResource($facture);
    }

    public function update(FactureRequest $request, Facture $facture)
    {
        if($facture->status === Facture::CANCELED) {
            return response()->json(['message' => 'Cannot update a canceled facture'], 400);
        };

       $facture = $this->factureService->updateFacture($facture,  $request->validated());

        if (!$facture) {
            return response()->json(['message' => 'Failed to update facture'], 500);
        }
        return $facture;
    }


    public function destroy(Facture $facture): JsonResponse
    {
        $this->factureService->deleteFacture($facture);

        return response()->json(null, 204);
    }

    public function cancel(Facture $facture): JsonResponse
    {
        $sucscess = $this->factureService->cancelFacture($facture);
        if(!$sucscess) {
            return response()->json(['message' => 'Failed to cancel facture'], 500);
        }
        return response()->json(['message' => 'Facture canceled successfully']);
    }

    // public function sendFactureToEmail(Request $request, Facture $facture)
    // {
    //     try {
    //         $request->validate([
    //             'htmlContent' => 'required|string'
    //         ]);
    
    //         $user = Auth::user();
    //         $htmlContent = $request->input('htmlContent');    
    //         // Generate PDF
    //         $pdf = Pdf::loadHTML($htmlContent);
    //         $pdfContent = $pdf->output();
    
    //         // Save PDF locally
    //         $pdfFileName = 'facture-' . $facture->reference . '.pdf';
    //         $pdfPath = storage_path('app/public/facture/' . $pdfFileName);
            
    //         // Ensure the directory exists
    //         if (!file_exists(dirname($pdfPath))) {
    //             mkdir(dirname($pdfPath), 0755, true);
    //         }
    
    //         // Save the PDF file
    //         file_put_contents($pdfPath, $pdfContent);
    
    //         // Send email with the locally stored PDF
    //         Mail::to($user->email)->send(new FacturePdfMail($pdfPath, $facture));
    
    //         return response()->json([
    //             'message' => 'Facture sent successfully to ' . $user->email
    //         ]);
    
    //     } catch (\Exception $e) {
    //         Log::error('Facture email error: ' . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         return response()->json([
    //             'message' => 'Failed to send facture',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Jobs\UpdateProductStockFromOrder;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPdfMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $orderService;


    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $order = $this->orderService->getAllOrder($searchTerm, $perPage);
        return response()->json($order);
    }

    public function store(OrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());
        UpdateProductStockFromOrder::dispatch($order, 'subtract');
        return response()->json($order, 201);
    }

    public function show(Order $order): JsonResponse
    {
        $order = $this->orderService->getOrder($order);
        return response()->json($order);
    }

    public function update(OrderRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->updateOrder($order, $request->validated());
        return $order
            ? response()->json($order)
            : response()->json(['message' => 'Order not found'], 404);
    }

    public function destroy(Order $order): JsonResponse
    {
        $success = $this->orderService->deleteOrder($order);
        return response()->json(null, 204);
    }
    
    public function sendOrderToEmail(Request $request, Order $order)
    {
        try {
            $request->validate([
                'htmlContent' => 'required|string'
            ]);
    
            $user = Auth::user();
            $htmlContent = $request->input('htmlContent');    
    
            // Generate PDF
            $pdf = Pdf::loadHTML($htmlContent);
            $pdfContent = $pdf->output();
    
            // Generate a unique file name using reference and current timestamp
            $timestamp = now()->timestamp;

            $pdfFileName = 'order-' . $order->reference . '-' . $timestamp . '.pdf';
            $pdfPath = "public/order/" . $pdfFileName;
    
            // Store the file using Laravel's Storage
            Storage::put($pdfPath, $pdfContent);
    
            // Send email with the stored file
            Mail::to($user->email)->send(new OrderPdfMail(Storage::path($pdfPath), $order));
    
            return response()->json([
                'message' => 'Order saved and sent successfully to ' . $user->email,
                'file_url' => asset("storage/order/$pdfFileName") // Public URL
            ]);
    
        } catch (\Exception $e) {
            Log::error('Order email error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to send order',
                'error' => $e->getMessage()
            ], 500);

}
    }



    public function publishToFacture(Order $order)
    {
        try {
            $facture = $this->orderService->publishToFacture($order);

            return response()->json([
                'success' => true,
                'message' => 'Order published to facture successfully.',
                'data' => $facture,
            ]);

        } catch (ValidationException $e) {
            // Stock or business rule errors
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Unexpected errors
            Log::error("Failed to publish order to facture: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while publishing the order.',
            ], 500);
        }
    }

}

<?php

namespace App\Services;

use App\Models\Facture;
use App\Repositories\FactureRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class FactureService
{
    protected $repository;
    protected $paymentRepository;

    public function __construct(FactureRepository $repository , PaymentRepository $paymentRepository)
    {
        $this->repository = $repository;
        $this->paymentRepository = $paymentRepository;
    }

    public function getAllFacture($searchTerm = null, $perPage = 10)
    {
        $factureCollection = $this->repository->getAllWithSearch($searchTerm, $perPage);
        // Format the products array for each Facture
        
        return $factureCollection;
    }

    public function getFacture(Facture $facture)
    {
      
       return  $facture = $this->repository->find($facture);
    }

    // public function createFacture(array $data)
    // {
    //     $facture =  $this->repository->create($data);
    //     return $this->formatProducts($facture);
    // }

    public function updateFacture(Facture $facture, $data)
    {
        try {
            DB::transaction(function () use ($facture, $data) {
                // Sync payments
                $this->paymentRepository->syncPayments($facture->id, $data['payments'] ?? []);

                // Calculate total paid
                $paid_amount = collect($data['payments'] ?? [])
                    ->sum(fn($payment) => $payment['amount'] ?? 0);

                // Update facture
                $facture->paid_amount = $paid_amount;

                $facture->status = Facture::getStatus($paid_amount, $facture->total);

                $facture->save();
            });

            return true;

        } catch (\Exception $exception) {
            Log::error('Error updating facture: ' . $exception->getMessage());
            return false;
        }
    }


    public function deleteFacture(Facture $facture)
    {
        return $this->repository->delete($facture);
    }


    public function cancelFacture(Facture $facture)
    {
        DB::beginTransaction();

        try {      

            // Check if facture is cancelable
            if (!$facture->status === Facture::PARTIALLY_PAID || !$facture->status === Facture::PAID || $facture->status === Facture::CANCELED) {
                return false;
            }

            // Update status
            $facture->status = Facture::CANCELED;
            $facture->save();

            // Load products relation
            $facture->load('products','order');

            // Decrement stock for each product
            $facture->products->each(function ($product) {
                $quantity = $product->pivot->quantity;
                $product->increment('stock', $quantity);
            });

            // remove ispublished from order
            if ($facture->order) {
                $facture->order->is_published = false;
                $facture->order->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error canceling facture: ' . $exception->getMessage());
            return false;
        }
    }


    public function formatProducts(Facture $facture): Facture
    {
        $formattedProducts = $facture->products->map(function ($product) {
            $productArray = $product->toArray();
            $pivotData = $productArray['pivot'];

            // Remove the pivot entry
            unset($productArray['pivot']);

            // Merge pivot data into the main product array
            return array_merge($productArray, $pivotData);
        });

        // Update the products relation in the Facture model
        $facture->setRelation('products', $formattedProducts);

        return $facture;
    }
}

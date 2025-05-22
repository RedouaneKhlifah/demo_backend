<?php

namespace App\Services;

use App\Jobs\UpdateProductStockFromFacture;
use App\Models\Order;
use App\Repositories\FactureRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderService
{
    protected $repository;
    protected $factureRepository;
    protected $sharedService;

    public function __construct(OrderRepository $repository , FactureRepository $factureRepository, SharedService $sharedService)
    {
        $this->factureRepository = $factureRepository;
        $this->sharedService = $sharedService;
        $this->repository = $repository;
    }

    public function getAllOrder($searchTerm = null, $perPage = 10)
    {
        $orderCollection = $this->repository->getAllWithSearch($searchTerm, $perPage);
        // Format the products array for each Order
        $orderCollection->getCollection()->transform(function ($order) {
            return $this->formatProducts($order);
        });
        
        return $orderCollection;
    }

    public function getOrder(Order $order)
    {
       $order = $this->repository->find($order);
       return $this->formatProducts($order); 
    }

    public function createOrder(array $data)
    {
        $order =  $this->repository->create($data);
        return $this->formatProducts($order);
    }

    public function updateOrder(Order $order, array $data)
    {
        $updatedOrder = $this->repository->update($order, $data);
        return $this->formatProducts($updatedOrder);

    }

    public function deleteOrder(Order $order)
    {
        return $this->repository->delete($order);
    }

    public function formatProducts(Order $order): Order
    {
        $formattedProducts = $order->products->map(function ($product) {
            $productArray = $product->toArray();
            $pivotData = $productArray['pivot'];

            // Remove the pivot entry
            unset($productArray['pivot']);

            // Merge pivot data into the main product array
            return array_merge($productArray, $pivotData);
        });

        // Update the products relation in the Order model
        $order->setRelation('products', $formattedProducts);

        return $order;
    }

    public function publishToFacture(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $facture = $this->factureRepository->createFromOrder($order);

            if (!$facture) {
                throw ValidationException::withMessages(['facture' => 'Failed to create facture.']);
            }

            foreach ($facture->products as $product) {
                $quantity = $product->pivot->quantity;

                // Check for deleted product
                if ($product->deleted_at !== null) {
                    throw ValidationException::withMessages([
                        'product' => "The product '{$product->name}' has been deleted and cannot be used."
                    ]);
                }

                // Check for insufficient stock
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for product '{$product->name}'. Required: {$quantity}, Available: {$product->stock}"
                    ]);
                }
            }

            // All products valid → now decrement stock
            foreach ($facture->products as $product) {
                $quantity = $product->pivot->quantity;
                $product->decrement('stock', $quantity);
            }
            
            // mark the order as published
            $order->update(['is_published' => true]);

            return $facture;
        });
    }


}

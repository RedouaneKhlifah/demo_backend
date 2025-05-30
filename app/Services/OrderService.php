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
        return $orderCollection;
    }

    public function getOrder(Order $order)
    {
       $order = $this->repository->find($order);
       return $order;
    }

    public function createOrder(array $data)
    {
        $order =  $this->repository->create($data);
        return $order;
    }

    public function updateOrder(Order $order, array $data)
    {
        $updatedOrder = $this->repository->update($order, $data);
        return $updatedOrder;

    }

    public function deleteOrder(Order $order)
    {
        return $this->repository->delete($order);
    }

    // public function formatProducts(Order $order): Order
    // {
    //     $formattedProducts = $order->products->map(function ($product) {
    //         $productArray = $product->toArray();
    //         $pivotData = $productArray['pivot'];

    //         // Remove the pivot entry
    //         unset($productArray['pivot']);

    //         // Merge pivot data into the main product array
    //         return array_merge($productArray, $pivotData);
    //     });

    //     // Update the products relation in the Order model
    //     $order->setRelation('products', $formattedProducts);

    //     return $order;
    // }

    public function publishToFacture(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->loadMissing('products'); // Avoid multiple DB hits

            $facture = $this->factureRepository->createFromOrder($order);

            if (!$facture) {
                throw ValidationException::withMessages(['facture' => 'Failed to create facture.']);
            }

            foreach ($order->products as $product) {
                $quantity = $product->pivot->quantity;

                // Validation: Product soft-deleted
                if ($product->trashed()) {
                    throw ValidationException::withMessages([
                        'product' => __('order.messages.product_deleted', ['name' => $product->name])
                    ]);
                }

                // Validation: Stock
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => __('order.messages.insufficient_stock', [
                            'name'      => $product->name,
                            'required'  => $quantity,
                            'available' => $product->stock,
                        ])
                    ]);
                }

                // Safe to decrement now
                $product->decrement('stock', $quantity);
            }

            $order->update(['is_published' => true]);

            return $facture;
        });
    }


}

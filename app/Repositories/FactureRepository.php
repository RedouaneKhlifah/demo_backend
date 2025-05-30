<?php

namespace App\Repositories;

use App\Models\Facture;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FactureRepository
{
    protected $model;

    public function __construct(Facture $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->with(['client', 'products']);

        if ($searchTerm = trim($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('reference', 'like', "%{$searchTerm}%")
                    ->orWhereHas('client', function ($q) use ($searchTerm) {
                        $q->where('company', 'like', "%{$searchTerm}%");
                    });
                });
        }
                    

        return $query->orderBy('id')->paginate($perPage);
    }

    public function find(Facture $facture)
    {
        return $facture->load([ 'client' ,'products','payments' => function ($query) {
                                                                    $query->orderBy('payment_date', 'asc');
                                                                }
                            ]);

    }

    // public function create(array $data)
    // {
    //     $facture = $this->model->create($data);
        
    //     // If products are included in the data, attach them
    //     if (isset($data['products'])) {
    //         $products = collect($data['products'])->mapWithKeys(function ($item) {
    //             return [$item['product_id'] => [
    //                 'price_unitaire' => $item['price_unitaire'],
    //                 'quantity' => $item['quantity']
    //             ]];
    //         });
    //         $facture->products()->attach($products);
    //     }

    //     return $facture->load(['products' , 'order', 'client']);
    // }

    public function update(Facture $facture, array $payments)
    {
        // First update the facture model with basic data
        $facture->update($payments);
        
        // Reload the model with its relationships
        return $facture;
    }
    public function delete(Facture $facture)
    {
        return $facture->delete();
    }

    public function getPartialStatusData($startDate, $endDate): array
    {
        $factures = Facture::with('products')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->filter(function ($facture) {
                return  $facture->paid_amount > 0 && ($facture->paid_amount < $facture->totals);
            });



        return [
            'total' => $factures->sum('totals'),
            'paid_amount' => $factures->sum('paid_amount')
        ];
    }

    public function getPaidPartialCompleteSum($startDate, $endDate): float
    {
        return Facture::with('products')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->filter(function ($facture) {
                return $facture->paid_amount > 0;
            })
            ->sum('paid_amount');
    }

    public function getProfit($startDate, $endDate): float
    {     
        $revenue = $this->getRevenue($startDate, $endDate);

        $costProducts = Facture::with(['products']) // No need for `withoutTrashed()` as trashed products are excluded by default
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()       
            ->filter(function ($facture) {
                return $facture->paid_amount > 0;
            })
            ->flatMap(fn(Facture $facture) => $facture->products->map(fn($product) => [
                'profit' =>   $product->cost_price * $product->pivot->quantity,
            ]))
            ->sum('profit');
            

            return $revenue - $costProducts;
    }


    public function getRevenue($startDate, $endDate): float
    {
        $revenue = Facture::whereBetween('created_at', [$startDate, $endDate])
            ->get()
        ->filter(function ($facture) {
                return $facture->paid_amount > 0;
            })
            ->sum('totals');

            return $revenue;
    }

    public function getFacturesForChart($startDate, $endDate): array
    {
        // Get all factures between the provided start and end date
        $factures = Facture::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($facture) {
                return [
                    'date' => $facture->created_at->format('Y-m-d'), // format the date to YYYY-MM-DD
                    'value' => $facture->totals, // or any other relevant metric for the chart
                ];
            });

        return $factures->toArray(); // Return the result in the required format for the chart
    }

    public function createFromOrder(Order $order): ?Facture
    {

        // Access the loaded products relation directly as Eloquent models
        $products = $order->products;

        // Calculate subtotal directly from the Eloquent models
        $subtotal = collect($products)->sum(function ($product) {
            return $product->pivot->price_unitaire * $product->pivot->quantity;
        });

        // Calculate TVA (tax)
        $tva = $order->tva && $order->tva > 0 ? ($subtotal * $order->tva / 100) : 0;

        $discount = $order->remise;

        if ($order->remise_type === 'PERCENT') {
            $discount = $subtotal * ($order->remise) / 100;
        }

        // Prevent discount from exceeding subtotal
        $discount = min($discount, $subtotal);

        // Final total
        $total = round($subtotal + $tva - $discount, 2);

        try {
            $facture = Facture::create([
                'order_id'         => $order->id,
                'client_id'        => $order->client_id,
                'reference'        => $order->reference,
                'bcn'              => $order->bcn,
                'facture_date'     => $order->order_date,
                'expiration_date'  => $order->expiration_date,
                'tva'              => $order->tva,
                'remise_type'      => $order->remise_type,
                'remise'           => $order->remise,
                'note'             => $order->note,
                'paid_amount'      => 0,
                'total'            => $total,
                'status'           => Facture::DRAFT,
            ]);

            // Map products with pivot data for attach
            $facture->products()->attach(
                $products->mapWithKeys(fn ($product) => [
                    $product->id => [
                        'price_unitaire' => $product->pivot->price_unitaire,
                        'quantity'       => $product->pivot->quantity,
                    ]
                ])->toArray()
            );

            return $facture->load(['products']);

        } catch (\Throwable $e) {
            Log::error("Failed to create facture from order [ID: {$order->id}]: {$e->getMessage()}");
            return null;
        }
    }
    
}
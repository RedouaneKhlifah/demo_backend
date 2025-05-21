<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateProductStockFromOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order, public string $action)
    {
    }

    public function handle(): void
    {    
        $this->order->products->each(function ($product) {
            $quantity = $product->pivot->quantity;

            if ($this->action === 'subtract') {
                $product->decrement('stock', $quantity);
            } else {
                $product->increment('stock', $quantity);
            }
        });
    }
}
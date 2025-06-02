<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $subtotal;
    public $tva;
    public $total;
    public $totalInWords;
    protected $pdfPath;

    public $locale;

    public function __construct($pdfPath, Order $order )
    {
        $this->pdfPath = $pdfPath;
        $this->order = $order;
        $this->locale = app()->getLocale();

        $this->calculateAmounts($order);
        $this->totalInWords = $this->convertTotalToWords($this->total , $this->locale);
    }

    protected function convertTotalToWords($amount , $locale = 'en')
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($amount));
    }


    protected function calculateAmounts(Order $order)
    {
        $this->subtotal = $order->products->reduce(function ($sum, $product) {
            return $sum + ($product->pivot->price_unitaire ?? 0) * ($product->pivot->quantity ?? 0);
        }, 0);

        $this->tva = ($this->subtotal * ($order->tva ?? 0)) / 100;
        $ttc = $this->subtotal + $this->tva;

        $remise = 0;
        if ($order->remise_type === 'PERCENT') {
            $remise = ($this->subtotal * ($order->remise ?? 0)) / 100;
        } else {
            $remise = $order->remise ?? 0;
        }

        $this->total = max($ttc - $remise, 0);
    }

    public function build()
    {
        return $this->subject('Order Details - ' . $this->order->reference)
                    ->view('emails.orderFile')
                    ->with([
                        'order' => $this->order,
                        'subtotal' => $this->subtotal,
                        'tva' => $this->tva,
                        'total' => $this->total,
                        'totalInWords' => $this->totalInWords,
                        'locale' => app()->getLocale(),
                    ])
                    ->attach($this->pdfPath, [
                        'as' => 'order-' . $this->order->reference . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}

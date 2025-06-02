<!DOCTYPE html>
<html dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans("order.Order") }} {{ $order->reference }}</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        p {
            margin: 0;
            padding: 0;
            color: #4b5563;
        }

        .container {
            padding: 2rem;
            max-width: 64rem;
            margin: 8rem auto 0 auto;
        }

        .section {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
        }

        .info {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #4b5563;
        }

        .info span {
            font-weight: 500;
        }

        .client-info {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 12px;
        }

        .client-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            margin-top: 0;
            font-size: 1rem;
        }

        .client-details {
            font-size: 0.875rem;
            color: #4b5563;
        }

        .client-row {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .order-table thead {
            background-color: #f3f4f6;
        }

        .order-table th {
            padding: 0.75rem 1rem;
            text-align: {{ $locale == 'ar' ? 'right' : 'left' }};
            font-weight: 600;
            color: #374151;
        }

        .order-table td {
            padding: 0.5rem 1rem;
            text-align: {{ $locale == 'ar' ? 'right' : 'left' }};
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-box {
            margin-left: auto;
            width: 320px;
            background-color: #f9fafb;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
            color: #4b5563;
            font-size: 0.875rem;
        }

        .summary-row.total {
            border-top: 1px solid #e5e7eb;
            padding-top: 0.5rem;
            font-weight: 600;
            color: #1f2937;
        }

        .total-in-words-container {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
        }

        .total-in-words-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .total-in-words {
            font-weight: 500;
            font-style: italic;
            color: #374151;
        }

    </style>
</head>
<body>
<div class="container">
    {{-- document info --}}
    <div class="section">
        <div class="header">
            <img src="{{ asset('images/logo.svg') }}" style="width: 64px; height: 64px" width="64" height="64" alt="Logo">
            <h2 class="title">{{ trans('order.delivery_note') }}</h2>
        </div>
        <div class="info">
            <div>
                <span>{{ trans('order.reference') }}: {{ $order->reference }}</span>
            </div>
            <div>
                <span>{{ trans('order.date') }}: {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</span>
            </div>
            @if($order->bcn)
                <div>
                    <span>{{ trans('order.bcn') }}: {{ $order->bcn }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- client info --}}
    <div class="client-info">
        <h3 class="client-title">{{ trans('order.billed_to') }}</h3>
        <div class="client-details">
            <div class="client-row">
                <span>{{ trans('order.company') }} :</span>
                <p>{{ $order->client->company }}</p>
            </div>

            <div class="client-row">
                <span>{{ trans('order.address') }} :</span>
                <p>{{ $order->client->address }}</p>
            </div>

            <div class="client-row">
                <span>{{ trans('order.phone') }} :</span>
                <p>{{ $order->client->phone }}</p>
            </div>

            <div class="client-row">
                <span>{{ trans('order.ice') }} :</span>
                <p>{{ $order->client->ice }}</p>
            </div>
        </div>
    </div>

    {{-- order products table --}}
    <table class="order-table">
        <thead>
        <tr>
            <th>{{ trans('order.product') }}</th>
            <th>{{ trans('order.quantity') }}</th>
            <th>{{ trans('order.unit_price') }}</th>
            <th>{{ trans('order.total_ht') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->products as $product)
            @php
                $quantity = floatval($product->pivot->quantity);
                $price = floatval($product->pivot->price_unitaire);
                $total = $quantity * $price;
            @endphp
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($quantity, 3) }} {{ $product->unit }}</td>
                <td>{{ number_format($price, 2) }} MAD</td>
                <td>{{ number_format($total, 2) }} MAD</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row">
            <span>{{ trans('order.subtotal_ht') }}:</span>
            <span>{{ number_format($subtotal, 2) }} MAD</span>
        </div>
        <div class="summary-row">
            <span>{{ trans('order.vat') }} ({{ $order->tva }} %):</span>
            <span>{{ number_format($tva, 2) }} MAD</span>
        </div>
        @if($order->remise > 0)
            <div class="summary-row">
                <span>{{ trans('order.discount') }}:</span>
                <span>
                    {{ $order->remise_type === 'PERCENT' ? $order->remise . ' %' : number_format($order->remise, 2) . ' MAD' }}
                </span>
            </div>
        @endif
        <div class="summary-row total">
            <span>{{ trans('order.total_ttc') }}:</span>
            <span>{{ number_format($total, 2) }} MAD</span>
        </div>
    </div>

    {{-- total in words --}}
    <div class="total-in-words-container">
        <p class="total-in-words-label">
            {{ trans('order.montant_in_words') }}
        </p>
        <p class="total-in-words">
            {{ $totalInWords }}
        </p>
    </div>

</div>
</body>
</html>

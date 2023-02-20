<x-mail::message>

# The transaction with invoice number {{ $order->invoice_number }} is **Success**.
----------------------------------------------------------------------------------
<br>

## Here detail of the order :

### Product details

@php
$totalPriceProduct = 0;
@endphp
<x-mail::table>
    | Product | Quantity | Price |
    | - | :-: | :-: |
    @foreach ($order->products as $product)
    @php
    $totalPriceProduct += $product->pivot->total_price;
    @endphp
    | {{ $product->name }} | {{ $product->pivot->quantity }} | {{ currencyFormat($product->pivot->total_price) }}
    @endforeach
    | **Total Price Product** |  | **{{ currencyFormat($totalPriceProduct) }}**
</x-mail::table>

### Courier service details

@foreach (json_decode($order->courier_services) as $courier)
@if($loop->last)
@php
$totalCosts = $courier;
@endphp
@break
@endif
- {{ str_replace(',', ' ', $courier) }}
@endforeach

<h3>Total costs of courier services : {{ $totalCosts }}</h3>

<h3>Total price of the order : {{ currencyFormat($order->total_price) }}</h3>

Thanks, <br>
{{ config('app.name') }}
</x-mail::message>

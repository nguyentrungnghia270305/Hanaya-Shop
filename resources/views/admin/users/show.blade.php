{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container mx-auto">
    <h2 class="text-xl font-bold mb-4">User Details</h2>
    <div class="mb-4">
        <strong>Name:</strong> {{ $user->name }}<br>
        <strong>Email:</strong> {{ $user->email }}<br>
        <strong>Role:</strong> {{ $user->role }}<br>
        <strong>Created At:</strong> {{ $user->created_at }}<br>
    </div>
    <h3 class="text-lg font-semibold mb-2">Orders</h3>
    <ul>
        @foreach($orders as $order)
            <li>Order #{{ $order->id }} - Status: {{ $order->status }}</li>
        @endforeach
    </ul>
    <h3 class="text-lg font-semibold mt-4 mb-2">Cart</h3>
    <ul>
        @foreach($carts as $cart)
            <li>Product: {{ $cart->product->name ?? 'N/A' }} - Quantity: {{ $cart->quantity }}</li>
        @endforeach
    </ul>
</div>
@endsection

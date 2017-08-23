@extends('layouts.backstage')

@section('backstageContent')
@include('backstage._sub-navbar', ['page' => 'orders'])
<div class="bg-soft p-xs-y-5">
    <div class="container m-xs-b-4">
        <div class="m-xs-b-6">
            <h2 class="m-xs-b-2 text-lg">Overview</h2>
            <div class="card">
                <div class="card-section border-b">
                    <p class="m-xs-b-4">This show is {{ $concert->percentSoldOut() }}% sold out.</p>
                    <progress class="progress" value="{{ $concert->ticketsSold() }}" max="{{ $concert->totalTickets() }}">{{ $concert->percentSoldOut() }}</progress>
                </div>
                <div class="row">
                    <div class="col col-md-4 border-md-r">
                        <div class="card-section p-md-r-2 text-center text-md-left">
                            <h3 class="text-base wt-normal m-xs-b-1">Total Tickets Remaining</h3>
                            <div class="text-jumbo wt-bold">
                                {{ $concert->ticketsRemaining() }}
                            </div>
                        </div>
                    </div>
                    <div class="col col-md-4 border-md-r">
                        <div class="card-section p-md-x-2 text-center text-md-left">
                            <h3 class="text-base wt-normal m-xs-b-1">Total Tickets Sold</h3>
                            <div class="text-jumbo wt-bold">
                                {{ $concert->ticketsSold() }}
                            </div>
                        </div>
                    </div>
                    <div class="col col-md-4">
                        <div class="card-section p-md-l-2 text-center text-md-left">
                            <h3 class="text-base wt-normal m-xs-b-1">Total Revenue</h3>
                            <div class="text-jumbo wt-bold">
                                ${{ $concert->revenueInDollars() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <h2 class="m-xs-b-2 text-lg">Recent Orders</h2>
            <div class="card">
                <div class="card-section">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-left">Email</th>
                                <th class="text-left">Tickets</th>
                                <th class="text-left">Amount</th>
                                <th class="text-left">Card</th>
                                <th class="text-left">Purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->email }}</td>
                                    <td>{{ $order->ticketQuantity() }}</td>
                                    <td>${{ $order->formatted_amount }}</td>
                                    <td><span class="text-dark-soft">****</span> {{ $order->card_last_four }}</td>
                                    <td class="text-dark-soft">{{ $order->formatted_date }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

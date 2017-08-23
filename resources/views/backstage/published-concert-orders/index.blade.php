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
                            @foreach(range(1, 10) as $i)
                                <tr>
                                    <td>{{ collect(['john', 'jane', 'dave', 'donna'])->random() }}&#64;example.com</td>
                                    <td>{{ rand(1, 4) }}</td>
                                    <td>${{ number_format(rand(5000, 15000) / 100, 2) }}</td>
                                    <td><span class="text-dark-soft">****</span> 4242</td>
                                    <td class="text-dark-soft">July 18, 2017 @ 12:37pm</td>
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

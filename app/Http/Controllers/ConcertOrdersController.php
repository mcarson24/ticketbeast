<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;	
	}

    public function store($concertId)
    {
        $this->validate(request(), [
            'email' => 'required'
        ]);

    	$concert = Concert::find($concertId);

    	$this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

        $concert->orderTickets(request('email'), request('ticket_quantity'));

    	return response()->json([], 201);
    }
}

<?php

namespace App\Http\Controllers;

use App\Order;
use App\Concert;
use App\Reservation;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsRemainException;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;	
	}

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email'             => 'required|email',
            'ticket_quantity'   => 'required|min:1|integer',
            'payment_token'     => 'required'
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));
            $this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));

            $order = Order::forTickets($reservation->tickets(), $reservation->email(), $reservation->totalCost());
        	
            return response($order, 201);

        } catch(PaymentFailedException $e) {
            $reservation->cancel();
            return response([], 422);
        } catch (NotEnoughTicketsRemainException $e) {
            return response([], 422);
        }


    }
}

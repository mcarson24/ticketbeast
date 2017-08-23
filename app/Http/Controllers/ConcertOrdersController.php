<?php

namespace App\Http\Controllers;

use App\Concert;
use App\Billing\PaymentGateway;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Support\Facades\Mail;
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

            $order = $reservation->complete($this->paymentGateway, request('payment_token'));
        	
            Mail::to($order->email)->send(new OrderConfirmationEmail($order));

            return response()->json($order, 201);
        } catch(PaymentFailedException $e) {
            $reservation->cancel();
            return response(['Damn'], 422);
        } catch (NotEnoughTicketsRemainException $e) {
            return response([], 422);
        }
    }
}

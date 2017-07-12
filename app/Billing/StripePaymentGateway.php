<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Error\InvalidRequest;
use App\Billing\PaymentFailedException;

class StripePaymentGateway implements PaymentGateway
{
	private $apiKey;

	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	public function charge($amount, $token) 
	{
		try {
			Charge::create([
				'amount'	=> $amount,
				'currency'	=> 'usd',
				'source'	=> $token
			], ['api_key' => $this->apiKey]);
		} catch (InvalidRequest $e) {
			throw new PaymentFailedException;
		}
	}
}
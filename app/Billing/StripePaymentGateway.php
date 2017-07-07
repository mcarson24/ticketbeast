<?php

namespace App\Billing;

use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
	private $apiKey;

	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	public function charge($amount, $token) 
	{
		Charge::create([
			'amount'	=> 2500,
			'currency'	=> 'usd',
			'source'	=> $token
		], ['api_key' => $this->apiKey]);
	}
}
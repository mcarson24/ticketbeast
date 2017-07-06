<?php

use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class StripePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        // Create new StripePaymentGateay instance
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

		$token = \Stripe\Token::create([
			"card" => [
				"number" => "4242424242424242",
				"exp_month" => 1,
				"exp_year" => date('Y') + 1,
				"cvc" => "123"
			]
		], [
			'api_key' => config('services.stripe.secret')
		])->id;	

        // new charge with valid token
        $paymentGateway->charge(2500, $token);
        
        // verify that charge was completed successfully
        $lastCharge = \Stripe\Charge::all(
        	['limit' => 1],
        	['api_key' => config('services.stripe.secret')]
    	)['data'][0];

    	$this->assertEquals(2500, $lastCharge->amount);
    }
}

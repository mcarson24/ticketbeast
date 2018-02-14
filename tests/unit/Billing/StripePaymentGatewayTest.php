<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\StripePaymentGateway;

/** @group integration */
class StripePaymentGatewayTest extends TestCase
{
	use PaymentGatewayContractsTest;

	protected function getPaymentGateway()
	{
		return new StripePaymentGateway(config('services.stripe.secret'));
	}

	/** @test */
	public function ninety_percent_of_the_payment_is_transferred_to_the_desination_accout()
	{
	    $paymenyGateway = new StripePaymentGateway(config('services.stripe.secret'));

	    $paymenyGateway->charge(5000, $paymenyGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));

	    $lastStripeCharge = array_first(\Stripe\Charge::all(
        	['limit' => 1],
        	['api_key' => config('services.stripe.secret')]
    	)['data']);

    	$this->assertEquals(5000, $lastStripeCharge['amount']);
    	$this->assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $lastStripeCharge['destination']);

    	$transfer = \Stripe\Transfer::retrieve($lastStripeCharge['transfer'], ['api_key' => config('services.stripe.secret')]);
    	$this->assertEquals(4500, $transfer['amount']);
	}
}

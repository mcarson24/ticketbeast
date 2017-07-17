<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends TestCase
{
	use PaymentGatewayContractsTest;

	protected function getPaymentGateway()
	{
		return new FakePaymentGateway;
	}

	/** @test */
	public function charges_with_an_invalid_token_fail()
	{
		try {
		    $paymentGateway = new FakePaymentGateway;

		    $paymentGateway->charge(10000, 'invalid-test-token');
		} catch (PaymentFailedException $e) {
			return;
		}

		$this->fail('The payment should not have suceeded, but it did.');
	}

	/** @test */
	public function running_hook_before_the_first_charge()
	{
	    $paymentGateway = new FakePaymentGateway;
	    $timesCallbackRan = 0;

	    $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timesCallbackRan) {
		    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
	    	$timesCallbackRan++;
	    	$this->assertEquals(2500, $paymentGateway->totalCharges());
	    });

	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
	    $this->assertEquals(1, $timesCallbackRan);
    	$this->assertEquals(5000, $paymentGateway->totalCharges());
	}
}
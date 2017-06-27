<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends TestCase
{
	/** @test */
	public function charges_with_a_valid_test_token_are_successful()
	{
	    $paymentGateway = new FakePaymentGateway;

	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

	    $this->assertEquals(2500, $paymentGateway->totalCharges());
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
}
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
	public function order_is_not_created_when_payment_fails()
	{
		try {
		    $paymentGateway = new FakePaymentGateway;

		    $paymentGateway->charge(10000, 'invalid-test-token');
		} catch (PaymentFailedException $e) {
			return;
		}

		$this->fail('The payment suceeded when it shouldn\'t have.');
	}
}
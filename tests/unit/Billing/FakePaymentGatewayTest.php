<?php

use App\Billing\FakePaymentGateway;

class FakePaymentGatewayTest extends TestCase
{
	/** @test */
	public function charges_with_a_valid_test_token_are_successful()
	{
	    $paymentGateway = new FakePaymentGateway;

	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

	    $this->assertEquals(2500, $paymentGateway->totalCharges());
	}
}
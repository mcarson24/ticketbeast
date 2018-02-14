<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;

class FakePaymentGatewayTest extends TestCase
{
	use PaymentGatewayContractsTest;

	protected function getPaymentGateway()
	{
		return new FakePaymentGateway;
	}

	/** @test */
	public function can_get_total_charges_for_a_specific_account()
	{
	    $paymentGateway = new FakePaymentGateway;

	    $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test_account_0000');
	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_1234');
	    $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_account_1234');

	    $this->assertEquals(6500, $paymentGateway->totalChargesFor('test_account_1234'));
	}

	/** @test */
	public function running_hook_before_the_first_charge()
	{
	    $paymentGateway = new FakePaymentGateway;
	    $timesCallbackRan = 0;

	    $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timesCallbackRan) {
		    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_1234');
	    	$timesCallbackRan++;
	    	$this->assertEquals(2500, $paymentGateway->totalCharges());
	    });

	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_1234');
	    $this->assertEquals(1, $timesCallbackRan);
    	$this->assertEquals(5000, $paymentGateway->totalCharges());
	}
}

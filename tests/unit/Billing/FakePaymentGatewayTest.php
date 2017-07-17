<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends TestCase
{
	protected function getPaymentGateway()
	{
		return new FakePaymentGateway;
	}

	/** @test */
	public function can_fetch_charges_created_during_callback()
	{
	    $paymentGateway = $this->getPaymentGateway();

	    $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
	    $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

	    $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
		    $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
		    $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
	    });

	    $this->assertCount(2, $newCharges);
	    $this->assertEquals([4000, 5000], $newCharges->all());
	}

	/** @test */
	public function charges_with_a_valid_test_token_are_successful()
	{
	    $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
	        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });
        
        $this->assertCount(1, $newCharges);
    	$this->assertEquals(2500, $newCharges->sum());
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
<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
	use DatabaseMigrations;

	/** @test */
	public function customer_can_purchase_concert_tickets()
	{
		$paymentGateway = new FakePaymentGateway;
		$this->app->instance(PaymentGateway::class, $paymentGateway);

	    // Create a concert
	    $concert = create(Concert::class, [
	    	'ticket_price' => 3250
    	]);

	    // Purchase concert tickets
	    $this->json('POST', "concerts/{$concert->id}/orders", [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(201);
	    // Assert that an order exists for the customer
	    $this->assertEquals(9750, $paymentGateway->totalCharges());

	    // Assert the customer was charged the correct amount
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNotNull($order);
	    $this->assertEquals(3, $order->tickets()->count());
	}
}
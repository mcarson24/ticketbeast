<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp()
	{
		parent::setUp();

		$this->paymentGateway = new FakePaymentGateway;
		$this->app->instance(PaymentGateway::class, $this->paymentGateway);
	}

	protected function assertValidationError($key)
	{
		$this->assertResponseStatus(422);	
		$this->assertArrayHasKey($key, $this->decodeResponseJson());
	}

	/** @test */
	public function customer_can_purchase_concert_tickets()
	{
	    // Create a concert
	    $concert = create(Concert::class, [
	    	'ticket_price' => 3250
    	]);

	    // Purchase concert tickets
	    $this->json('POST', "concerts/{$concert->id}/orders", [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(201);
	    // Assert that an order exists for the customer
	    $this->assertEquals(9750, $this->paymentGateway->totalCharges());

	    // Assert the customer was charged the correct amount
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNotNull($order);
	    $this->assertEquals(3, $order->tickets()->count());
	}

	/** @test */
	public function email_is_required_to_purchase_tickets()
	{
		$concert = create(Concert::class);

		$this->json('POST', "concerts/{$concert->id}/orders", [
			'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('email');
		
	}
}
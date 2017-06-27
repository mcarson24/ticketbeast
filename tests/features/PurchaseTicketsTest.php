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

		$this->concert = factory(Concert::class)->states('published')->create();
	}

	private function assertValidationError($field)
	{
		$this->assertResponseStatus(422);	
		$this->assertArrayHasKey($field, $this->decodeResponseJson());
	}

	private function orderTickets($concert, $parameters)
	{
		$this->json('POST', "concerts/{$concert->id}/orders", $parameters);
	}

	/** @test */
	public function customer_can_purchase_concert_tickets_to_a_published_concert()
	{
	    // Create a concert
	    $concert = factory(Concert::class)->states('published')->create([
	    	'ticket_price' => 3250
    	]);
    	$concert->addTickets(3);

	    $this->orderTickets($concert, [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(201);
	    $this->assertEquals(9750, $this->paymentGateway->totalCharges());

	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNotNull($order);
	    $this->assertEquals(3, $order->tickets()->count());
	}

	/** @test */
	public function customer_cannot_purchase_tickets_to_an_unpublished_concert()		
	{
	    $concert = factory(Concert::class)->states('unpublished')->create();
	    $concert->addTickets(3);

	    $this->orderTickets($concert, [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(404);
    	$this->assertEquals(0, $this->paymentGateway->totalCharges());
    	$this->assertEquals(0, $concert->orders()->count());
	}

	/** @test */
	public function cannot_purchase_more_tickets_than_remain()
	{
	    $concert = factory(Concert::class)->states('published')->create();
	    $concert->addTickets(50);

	    $this->orderTickets($concert, [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 51,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(422);
    	$this->assertNull($concert->orders()->where('email', 'john@example.com')->first());
    	$this->assertEquals(0, $this->paymentGateway->totalCharges());
    	$this->assertEquals(50, $concert->ticketsRemaining());
	}

	/** @test */
	public function an_order_is_not_created_when_payment_fails()
	{
    	$this->concert->addTickets(3);
    	
    	$this->orderTickets($this->concert, [
    		'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> 'invalid-test-token'
    	]);

    	$this->assertResponseStatus(422);
    	$this->assertNull($this->concert->orders()->where('email', 'john@example.com')->first());
	}

	/** @test */
	public function email_is_required_to_purchase_tickets()
	{
		$this->orderTickets($this->concert, [
			'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('email');
	}

	/** @test */
	public function email_must_be_valid_to_purchase_tickets()
	{
	    $this->orderTickets($this->concert, [
	    	'email' 	=> 'janeexample.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertValidationError('email');
	}

	/** @test */
	public function ticket_quantity_is_required_to_purchase_tickets()
	{
	    $this->orderTickets($this->concert, [
	    	'email' 	=> 'janeexample.com',
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertValidationError('ticket_quantity');
	}

	/** @test */
	public function ticket_quantity_must_be_greater_than_zero()
	{
	    $this->orderTickets($this->concert, [
	    	'email' 	=> 'jane@example.com',
	    	'ticket_quantity' 	=> 0,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertValidationError('ticket_quantity');
	}

	/** @test */
	public function ticket_quantity_must_be_an_integer()
	{
	 	$this->json('POST', "concerts/{$this->concert->id}/orders", [
	    	'email' 	=> 'jane@example.com',
	    	'ticket_quantity' 	=> 's',
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertValidationError('ticket_quantity');   	
	}

	/** @test */
	public function a_payment_token_is_required()
	{
	 	$this->json('POST', "concerts/{$this->concert->id}/orders", [
	    	'email' 	=> 'jane@example.com',
	    	'ticket_quantity' 	=> 's',
    	]);

    	$this->assertValidationError('payment_token');   
	}
}
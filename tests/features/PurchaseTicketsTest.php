<?php

use App\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
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
		$requestA = $this->app['request'];
		$this->json('POST', "concerts/{$concert->id}/orders", $parameters);
		$this->app['request'] = $requestA;
	}

	/** @test */
	public function customer_can_purchase_concert_tickets_to_a_published_concert()
	{
	    $concert = factory(Concert::class)->states('published')->create([
	    	'ticket_price' => 3250
    	])->addTickets(3);

	    $this->orderTickets($concert, [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(201);

    	$this->seeJsonSubset([
    		'email' 			=> 'john@example.com',
    		'ticket_quantity'	=> 3,
    		'amount' 			=> 9750
		]);

	    $this->assertEquals(9750, $this->paymentGateway->totalCharges());

	    $this->assertTrue($concert->hasOrderFor('john@example.com'));
	    $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
	}

	/** @test */
	public function customer_cannot_purchase_tickets_to_an_unpublished_concert()		
	{
	    $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

	    $this->orderTickets($concert, [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(404);
    	$this->assertEquals(0, $this->paymentGateway->totalCharges());
    	$this->assertFalse($concert->hasOrderFor('john@example.com'));
	}

	/** @test */
	public function cannot_purchase_more_tickets_than_remain()
	{
	    $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

	    $this->orderTickets($concert, [
	    	'email' 			=> 'john@example.com',
	    	'ticket_quantity' 	=> 51,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertResponseStatus(422);
    	$this->assertFalse($concert->hasOrderFor('john@example.com'));
    	$this->assertEquals(0, $this->paymentGateway->totalCharges());
    	$this->assertEquals(50, $concert->ticketsRemaining());
	}

	/** @test */
	public function cannot_purchase_tickets_that_another_user_is_trying_to_purchase()
	{
		$this->disableExceptionHandling();

		$concert = factory(Concert::class)->states('published')->create([
			'ticket_price' 	=> 1200
		])->addTickets(3);

    	$this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
    		$this->orderTickets($concert, [
		    	'email' 			=> 'personB@example.com',
		    	'ticket_quantity' 	=> 1,
		    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
	    	]);

    		$this->assertResponseStatus(422);
	    	$this->assertFalse($concert->hasOrderFor('personB@example.com'));
	    	$this->assertEquals(0, $this->paymentGateway->totalCharges());
    	});

	    $this->orderTickets($concert, [
	    	'email' 			=> 'personA@example.com',
	    	'ticket_quantity'	=> 3,
	    	'payment_token'		=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertEquals(3600, $this->paymentGateway->totalCharges());
	    $this->assertTrue($concert->hasOrderFor('personA@example.com'));
	    $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
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
    	$this->assertFalse($this->concert->hasOrderFor('john@example.com'));
	}

	/** @test */
	public function email_is_required_to_purchase_tickets()
	{
		$this->concert->addTickets(35);

		$this->orderTickets($this->concert, [
			'ticket_quantity' 	=> 3,
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('email');
	}

	/** @test */
	public function email_must_be_valid_to_purchase_tickets()
	{
		$this->concert->addTickets(35);

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
		$this->concert->addTickets(35);

	    $this->orderTickets($this->concert, [
	    	'email' 	=> 'janeexample.com',
	    	'payment_token' 	=> $this->paymentGateway->getValidTestToken()
    	]);

    	$this->assertValidationError('ticket_quantity');
	}

	/** @test */
	public function ticket_quantity_must_be_greater_than_zero()
	{
		$this->concert->addTickets(35);

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
		$this->concert->addTickets(35);

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
		$this->concert->addTickets(35);

	 	$this->json('POST', "concerts/{$this->concert->id}/orders", [
	    	'email' 	=> 'jane@example.com',
	    	'ticket_quantity' 	=> 's',
    	]);

    	$this->assertValidationError('payment_token');   
	}
}
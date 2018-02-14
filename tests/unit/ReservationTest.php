<?php

namespace Tests\Unit;

use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReservationTest extends TestCase
{
	use DatabaseMigrations;

	/** @test */
	public function calculating_the_total_cost()
	{
	    $tickets = collect([
	    	(object) ['price' => 1200],
	    	(object) ['price' => 1200],
	    	(object) ['price' => 1200],
    	]);

	    $reservation = new Reservation($tickets, 'someone@example.com');

	    $this->assertEquals(3600, $reservation->totalCost());
	}

	/** @test */
	public function reserved_tickets_are_released_when_a_reservation_is_cancelled()
	{
		$tickets = collect([
			\Mockery::spy(Ticket::class),
			\Mockery::spy(Ticket::class),
			\Mockery::spy(Ticket::class)
		]);

		$reservation = new Reservation($tickets, 'someone@example.com');

		$reservation->cancel();

		$tickets->each(function($ticket) {
			$ticket->shouldHaveReceived('release');
		});
	}

	/** @test */
	public function can_retrieve_tickets_from_reservations()
	{
	    $tickets = collect([
	    	(object) ['price' => 1200],
	    	(object) ['price' => 1200],
	    	(object) ['price' => 1200],
    	]);

    	$reservation = new Reservation($tickets, 'someone@example.com');

    	$this->assertEquals($tickets, $reservation->tickets());
	}

	/** @test */
	public function can_retrieve_customers_email_for_a_reservation()
	{
		$tickets = collect();

	    $reservation = new Reservation($tickets, 'holly@thedog.com');

	    $this->assertEquals('holly@thedog.com', $reservation->email());
	}

	/** @test */
	public function completing_a_reservation()
	{	
		$concert = factory(Concert::class)->create(['ticket_price' => 1000]);
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
	    $reservation = new Reservation($tickets, 'john@example.com');
	    $paymentGateway = new FakePaymentGateway;

	    $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken(), 'test_account_1234');

	    $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3000, $order->amount);
        $this->assertEquals(3000, $paymentGateway->totalCharges());
        $this->assertEquals(3000, $paymentGateway->totalChargesFor('test_account_1234'));
	}
}

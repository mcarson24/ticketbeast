<?php

use App\Reservation;

class ReservationTest extends TestCase
{
	/** @test */
	public function calculating_the_total_cost()
	{
	    $tickets = collect([
	    	(object) ['price' => 1200],
	    	(object) ['price' => 1200],
	    	(object) ['price' => 1200],
    	]);

	    $reservation = new Reservation($tickets);

	    $this->assertEquals(3600, $reservation->totalCost());
	}

	/** @test */
	public function reserved_tickets_are_released_when_a_reservation_is_cancelled()
	{
		$tickets = collect([
			Mockery::spy(Ticket::class),
			Mockery::spy(Ticket::class),
			Mockery::spy(Ticket::class)
		]);

		$reservation = new Reservation($tickets);

		$reservation->cancel();

		$tickets->each(function($ticket) {
			$ticket->shouldHaveReceived('release');
		});
	}
}

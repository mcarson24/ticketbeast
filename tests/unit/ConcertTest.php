<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Exceptions\NotEnoughTicketsRemainException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_fromatted_date_with_day()
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2017-06-06 8:00pm')
        ]);

        $this->assertEquals('Tuesday, June 6th, 2017', $concert->formatted_date_with_day);
    }

    /** @test */
    function can_get_ticket_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function concerts_can_be_published()
    {
        $concert = factory(Concert::class)->states('unpublished')->create(['ticket_quantity' => 5]);

        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
        
        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals(5, $concert->ticketsRemaining());
    }

    /** @test */
    public function unpublished_concerts_do_not_have_any_tickets_available()
    {
        $concert = factory(Concert::class)->states('unpublished')->create(['ticket_quantity' => 5]);

        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    public function can_add_tickets_when_publishing_a_concert()
    {
        $concert = factory(Concert::class)->create(['ticket_quantity' => 5]);
        $this->assertEquals(0, $concert->ticketsRemaining());

        $concert->publish();

        $this->assertEquals(5, $concert->ticketsRemaining());
    }

    /** @test */
    public function can_reserve_available_tickets()
    {
        $concert = \ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'john@example.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('john@example.com', $reservation->email());
        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = \ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'joey@example.com');
        } catch (NotEnoughTicketsRemainException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail('There should not have been enough tickets remaining to fulfill the reservation.');
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased()
    {
        $concert = \ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $order = factory(Order::class)->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsRemainException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Reserving tickets suceeded even though the tickets were already sold.');
    }

    /** @test */
    public function tickets_remaining_does_not_includes_tickets_that_were_already_allocated()
    {
        $concert = factory(Concert::class)->create();

        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(2, $concert->ticketsRemaining());
    }

     /** @test */
    public function tickets_sold_only_includes_tickets_associated_with_an_order()
    {
        $concert = \ConcertFactory::createPublished();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => null]));

        $this->assertEquals(3, $concert->ticketsSold());
    }

    /** @test */
    public function total_tickets_includes_all_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => null]));

        $this->assertEquals(6, $concert->totalTickets());
    }

    /** @test */
    public function calculating_percentage_of_tickets_that_have_been_sold()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => null]));

        $this->assertEquals(28.57, $concert->percentSoldOut());
    }

    /** @test */
    public function trying_to_reserve_more_tickets_than_remain_throws_an_exception()
    {
        $concert = \ConcertFactory::createPublished(['ticket_quantity' => 10]);

        try {
            $concert->reserveTickets(24, 'holly@theDog.com');
        } catch (NotEnoughTicketsRemainException $e) {
            $this->assertFalse($concert->hasOrderFor('holly@thedog.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('The reservation suceeded even though there wasn\'t enough tickets left to fulfill it.');
    }
}

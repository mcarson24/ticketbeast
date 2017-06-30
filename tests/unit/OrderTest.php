<?php

use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function creating_an_order_from_tickets_email_and_amount()
    {
        $concert = create(Concert::class)->addTickets(5);
        
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order = Order::forTickets($concert->findTickets(3), 'holly@thedog.com', 3600);

        $this->assertEquals($order->email, 'holly@thedog.com');
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);

        $this->assertEquals(2, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    public function converting_to_an_array()
    {
        $concert = create(Concert::class, ['ticket_price' => 1200])->addTickets(5);

        $order = $concert->orderTickets('duchess@thedog.com', 5);

        $result = $order->toArray();

        $this->assertEquals([
            'email'             => 'duchess@thedog.com',
            'ticket_quantity'   => 5,
            'amount'            => 6000
        ], $result);
    }
}

<?php

use App\Concert;
use App\Order;
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

    /** @test */
    public function tickets_are_released_when_an_order_is_cancelled()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $order->tickets()->count());
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull($order->fresh());
    }
}

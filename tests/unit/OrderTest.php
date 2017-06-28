<?php

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderTest extends TestCase
{
	use DatabaseMigrations;

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

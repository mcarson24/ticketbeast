<?php

use App\Ticket;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class TicketTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_released_from_an_order()
    {
        $concert = create(Concert::class);
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();

        $this->assertEquals(1, $order->tickets()->count());
        $this->assertEquals($concert->id, $ticket->order_id);
        
        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
        // $this->assertEquals(0, $order->fresh()->tickets()->count());
    }

    /** @test */
    public function a_ticket_can_be_reserved()
    {
        $ticket = create(Ticket::class);
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }
}

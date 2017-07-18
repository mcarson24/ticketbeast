<?php

use App\Concert;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class TicketTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_released_from_an_order()
    {
        $ticket = factory(Ticket::class)->states('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_reserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function can_get_an_order_from_a_ticket()
    {
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create(['order_id' => $order->id]);

        $this->assertEquals($order->id, $ticket->order->id);
        $this->assertEquals($order->email, $ticket->order->email);
    }
}

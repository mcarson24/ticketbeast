<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Facades\TicketCode;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
    public function a_ticket_can_be_claimed_for_an_order()
    {
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create(['code' => NULL]);
        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        // Assert that the ticket is saved to the order
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        // Assert that the ticket had the expected ticket code generated
        $this->assertEquals('TICKETCODE1', $ticket->code);
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

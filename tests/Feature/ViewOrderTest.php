<?php

use Tests\Testcase;
use App\Order;
use App\Ticket;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewOrderTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        // Create a concert
        $concert = factory(Concert::class)->create();
        // Create an order
        $order = factory(Order::class)->create();
        // Create a ticket
        $tickets = factory(Ticket::class)->create([
        	'concert_id' => $concert->id, 
        	'order_id' => $order->id
    	]);

        // Visit the order confirmation page
        
        // Assert we see the correct order details
    }
}
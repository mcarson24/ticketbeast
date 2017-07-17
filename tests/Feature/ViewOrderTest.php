<?php

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\Testcase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewOrderTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $this->disableExceptionHandling();

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
        $response = $this->get("orders/ORDERCONFIRMATION1234");

        $response->assertStatus(200);
        // Assert we see the correct order details
        $response->assertViewHas('order', function($viewOrder) use ($order) {
            return $order->id == $viewOrder->id;
        });
    }
}
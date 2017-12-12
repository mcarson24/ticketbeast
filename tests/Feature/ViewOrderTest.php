<?php

namespace Tests\Feature;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\Testcase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewOrderTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $this->withExceptionHandling();

        // Create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('March 12, 2017 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555.',
        ]);
        // Create an order
        $order = factory(Order::class)->create([
            'confirmation_number'   => 'ORDERCONFIRMATION1234',
            'card_last_four'        => '1881',
            'amount'                => 8500,
            'email'                 => 'john@example.com'
        ]);
        // Create some tickets
        $ticketA = factory(Ticket::class)->create([
            'concert_id'    => $concert->id, 
            'order_id'      => $order->id,
            'code'          => 'TICKETCODE1234'
    	]);

        $ticketB = factory(Ticket::class)->create([
            'concert_id'    => $concert->id, 
            'order_id'      => $order->id,
            'code'          => 'TICKETCODE5678'
        ]);

        // Visit the order confirmation page
        $response = $this->get("orders/ORDERCONFIRMATION1234");

        $response->assertStatus(200);
        // Assert we see the correct order details
        $response->assertViewHas('order', function($viewOrder) use ($order) {
            return $order->id == $viewOrder->id;
        });

        $response->assertSee('ORDERCONFIRMATION1234')
                 ->assertSee('$85.00')
                 ->assertSee('**** **** **** 1881')
                 ->assertSee('TICKETCODE1234')
                 ->assertSee('TICKETCODE5678')
                 ->assertSee('The Red Chord')
                 ->assertSee('with Animosity and Lethargy')
                 ->assertSee('The Mosh Pit')
                 ->assertSee('123 Example Lane')
                 ->assertSee('Laraville, ON')
                 ->assertSee('17916')
                 ->assertSee('john@example.com')
                 ->assertSee('2017-03-12 20:00');
    }
}

<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use Tests\TestCase;
use App\Billing\Charge;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'holly@thedog.com', $charge);

        $this->assertEquals($order->email, 'holly@thedog.com');
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create(['confirmation_number' => 'CONFIRMATION1234']);

        $foundOrder = Order::findByConfirmationNumber('CONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function retrieving_a_non_existant_order_by_confirmation_order_throws_an_exception()
    {
        try {
            Order::findByConfirmationNumber('NONEXISTANTCONFIRMATION');
        } catch (ModelNotFoundException $e) {
            return;
        }
        $this->fail('No matching order was found for the matching confirmation number but, a ModelNotFoundException was not thrown.');
    }

    /** @test */
    public function converting_to_an_array()
    {
        $order = factory(Order::class)->create([
            'amount'                => 6000,
            'email'                 => 'holly@theDog.com',
            'confirmation_number'   => 'CONFIRMATION1234'
        ]);
        $order->tickets()->saveMany([
            factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        $this->assertEquals([
            'email'                 => 'holly@theDog.com',
            'amount'                => 6000,
            'confirmation_number'   => 'CONFIRMATION1234',
            'tickets'               => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3']
            ]
        ], $result);
    }
}

<?php

use App\Billing\Charge;
use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $tickets = factory(Ticket::class, 3)->create();
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

        $order = Order::forTickets($tickets, 'holly@thedog.com', $charge);

        $this->assertEquals($order->email, 'holly@thedog.com');
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
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
        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());

        $result = $order->toArray();

        $this->assertEquals([
            'email'                 => 'holly@theDog.com',
            'ticket_quantity'       => 5,
            'amount'                => 6000,
            'confirmation_number'   => 'CONFIRMATION1234'
        ], $result);
    }
}

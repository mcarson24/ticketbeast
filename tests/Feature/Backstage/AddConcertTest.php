<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertTest extends TestCase
{
	use DatabaseMigrations;

	private function from($url)
	{
		session()->setPreviousUrl(url($url));

		return $this;
	}

    /** @test */
    public function promoters_can_view_the_add_concerts_form()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('backstage/concerts/new');

        $response->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_the_add_concerts_form()
    {

        $response = $this->get('backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    /** @test */
    public function adding_a_valid_concert()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('backstage/concerts', [
        	'title'						=> 'No Warning',
        	'subtitle'					=> 'with Cruel Hand and Backtrack',
        	'additional_information'	=> 'You must be at least 19 years to attend this show.',
        	'date'						=> '2017-11-18',
        	'time'						=> '8:00pm',
        	'venue'						=> 'The Mosh Pit',
        	'venue_address'				=> '123 Fake St.',
        	'city'						=> 'Laraville',
        	'state'						=> 'ON',
        	'zip'						=> '12345',
        	'ticket_price'				=> '32.50',
        	'ticket_quantity'			=> '75'
    	]);

    	tap(Concert::first(), function($concert) use ($response) {
    		$response->assertStatus(302);
    		$response->assertRedirect("concerts/{$concert->id}");

    		$this->assertEquals('No Warning', $concert->title);
    		$this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
    		$this->assertEquals('You must be at least 19 years to attend this show.', $concert->additional_information);
    		$this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
    		$this->assertEquals('The Mosh Pit', $concert->venue);
    		$this->assertEquals('123 Fake St.', $concert->venue_address);
    		$this->assertEquals('Laraville', $concert->city);
    		$this->assertEquals('ON', $concert->state);
    		$this->assertEquals('12345', $concert->zip);
    		$this->assertEquals(3250, $concert->ticket_price);
    		$this->assertEquals(75, $concert->ticketsRemaining());
    	});
    }

    /** @test */
    public function guests_cannot_add_valid_concerts()
    {
        $response = $this->post('backstage/concerts', [
        	'title'						=> 'No Warning',
        	'subtitle'					=> 'with Cruel Hand and Backtrack',
        	'additional_information'	=> 'You must be at least 19 years to attend this show.',
        	'date'						=> '2017-11-18',
        	'time'						=> '8:00pm',
        	'venue'						=> 'The Mosh Pit',
        	'venue_address'				=> '123 Fake St.',
        	'city'						=> 'Laraville',
        	'state'						=> 'ON',
        	'zip'						=> '12345',
        	'ticket_price'				=> '32.50',
        	'ticket_quantity'			=> '75'
    	]);

    	$response->assertStatus(302);
    	$response->assertRedirect('login');
    	$this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function title_is_required()
    {
    	$this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
        			 	 ->post('backstage/concerts', [
        	'title'						=> '',
        	'subtitle'					=> 'with Cruel Hand and Backtrack',
        	'additional_information'	=> 'You must be at least 19 years to attend this show.',
        	'date'						=> '2017-11-18',
        	'time'						=> '8:00pm',
        	'venue'						=> 'The Mosh Pit',
        	'venue_address'				=> '123 Fake St.',
        	'city'						=> 'Laraville',
        	'state'						=> 'ON',
        	'zip'						=> '12345',
        	'ticket_price'				=> '32.50',
        	'ticket_quantity'			=> '75'
    	]);

    	$response->assertStatus(302);
    	$response->assertRedirect('backstage/concerts/new');
    	$this->assertEquals(0, Concert::count());
    	$response->assertSessionHasErrors('title');
    }
}

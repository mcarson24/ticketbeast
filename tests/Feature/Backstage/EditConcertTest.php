<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
	use DatabaseMigrations;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information.',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New Town',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price' => '99.99',
            'ticket_quantity' => '500'
        ], $overrides);
    }

    /** @test */
    public function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
    	$this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    /** @test */
    public function promoters_cannot_view_the_edit_form_for_other_promoters_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
        $otherUser = factory(User::class)->create();

        $response = $this->actingAs($otherUser)->get("backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }

    /** @test */
    public function promoters_see_a_404_when_attempting_to_view_the_edit_page_of_a_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('backstage/concerts/999/edit');

        $response->assertStatus(404);
    }

    /** @test */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
    	$user = factory(User::class)->create();
    	$usersConcert = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);

        $response = $this->get("backstage/concerts/{$usersConcert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    /** @test */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {	
     	$user = factory(User::class)->create();

        $response = $this->get("backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('login');   
    }

    /** @test */
    public function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'subtitle' => 'The Old Subtitle',
            'additional_information' => 'Old additional information.',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old Venue',
            'venue_address' => 'Old address',
            'city' => 'Old Town',
            'state' => 'Old State',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 5
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information.',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New Town',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price' => '99.99',
            'ticket_quantity' => 10
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts');
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('New Title', $concert->title);
            $this->assertEquals('New Subtitle', $concert->subtitle);
            $this->assertEquals('New additional information.', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-12 8:00pm'), $concert->date);
            $this->assertEquals('New Venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New Town', $concert->city);
            $this->assertEquals('New State', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals('9999', $concert->ticket_price);
            $this->assertEquals(10, $concert->ticket_quantity);
        });
    }

    /** @test */
    public function promoters_cannot_edit_another_users_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $otherUser->id,
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'additional_information' => 'Old additional information.',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old Venue',
            'venue_address' => 'Old address',
            'city' => 'Old Town',
            'state' => 'Old State',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 5,
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information.',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New Town',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price' => '99.99',
            'ticket_quantity' => '10',
        ]);

        $response->assertStatus(404);
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old Title', $concert->title);
            $this->assertEquals('Old Subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information.', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old Venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old Town', $concert->city);
            $this->assertEquals('Old State', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals('2000', $concert->ticket_price);
            $this->assertEquals('5', $concert->ticket_quantity);
        });
    }

    /** @test */
    public function promoters_cannot_edit_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'additional_information' => 'Old additional information.',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old Venue',
            'venue_address' => 'Old address',
            'city' => 'Old Town',
            'state' => 'Old State',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 5,
        ]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information.',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New Town',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price' => '99.99',
            'ticket_quantity' => '10',
        ]);

        $response->assertStatus(403);
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old Title', $concert->title);
            $this->assertEquals('Old Subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information.', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old Venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old Town', $concert->city);
            $this->assertEquals('Old State', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals('2000', $concert->ticket_price);
            $this->assertEquals('5', $concert->ticket_quantity);
        });
    }

    /** @test */
    public function guests_cannot_edit_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'additional_information' => 'Old additional information.',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old Venue',
            'venue_address' => 'Old address',
            'city' => 'Old Town',
            'state' => 'Old State',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->patch("backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information.',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New Town',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price' => '99.99',
        ]);

        $response->assertRedirect('login');
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old Title', $concert->title);
            $this->assertEquals('Old Subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information.', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old Venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old Town', $concert->city);
            $this->assertEquals('Old State', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals('2000', $concert->ticket_price);
        });
    }

    /** @test */
    public function title_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['title' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old Title', $concert->title);
        });
    }

    /** @test */
    public function subtitle_is_optional()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'subtitle' => 'Old Subtitle',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['subtitle' => '']));

        $response->assertRedirect("backstage/concerts/");

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('', $concert->subtitle);
        });
    }

    /** @test */
    public function aditional_information_is_optional()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'additional_information' => 'Old additional information.',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['additional_information' => '']));

        $response->assertRedirect("backstage/concerts/");

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('', $concert->additional_information);
        });
    }

    /** @test */
    public function date_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2017-01-01 5:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['date' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
        });
    }

    /** @test */
    public function date_must_be_a_valid_date()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2017-01-01 5:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['date' => 'invalid-date']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
        });
    }

    /** @test */
    public function time_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2017-01-01 5:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['time' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
        });
    }

    /** @test */
    public function time_must_be_a_valid_time()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2017-01-01 5:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['time' => 'not-a-valid-time']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
        });
    }

    /** @test */
    public function venue_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'venue' => 'Old Venue',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['venue' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old Venue', $concert->venue);
        });
    }

    /** @test */
    public function venue_address_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'venue_address' => 'Old address',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['venue_address' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue_address');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old address', $concert->venue_address);
        });
    }

    /** @test */
    public function city_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'city' => 'Old Town',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['city' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('city');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old Town', $concert->city);
        });
    }

    /** @test */
    public function state_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'state' => 'Old State',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['state' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('state');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('Old State', $concert->state);
        });
    }

    /** @test */
    public function zip_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'zip' => '00000',
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['zip' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('zip');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('00000', $concert->zip);
        });
    }


    /** @test */
    public function ticket_price_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('2000', $concert->ticket_price);
        });
    }

    /** @test */
    public function ticket_price_must_be_numeric()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => 'twenty']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('2000', $concert->ticket_price);
        });
    }

    /** @test */
    public function ticket_price_must_be_at_least_5()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_price' => 2000,
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => '4.99']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('2000', $concert->ticket_price);
        });
    }

    /** @test */
    public function ticket_quantity_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('5', $concert->ticket_quantity);
        });
    }

    /** @test */
    public function ticket_quantity_must_be_an_integer()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '5.34']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('5', $concert->ticket_quantity);
        });
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5
        ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
                         ->from("backstage/concerts/{$concert->id}/edit")
                         ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '0']));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals('5', $concert->ticket_quantity);
        });
    }
}
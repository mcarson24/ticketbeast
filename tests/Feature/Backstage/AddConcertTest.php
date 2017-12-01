<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title'                        => 'No Warning',
            'subtitle'                     => 'with Cruel Hand and Backtrack',
            'additional_information'       => 'You must be at least 19 years to attend this show.',
            'date'                         => '2017-11-18',
            'time'                         => '8:00pm',
            'venue'                        => 'The Mosh Pit',
            'venue_address'                => '123 Fake St.',
            'city'                         => 'Laraville',
            'state'                        => 'ON',
            'zip'                          => '12345',
            'ticket_price'                 => '32.50',
            'ticket_quantity'              => '75',
        ], $overrides);
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
        Storage::fake('s3');
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('backstage/concerts', [
            'title'                         => 'No Warning',
            'subtitle'                      => 'with Cruel Hand and Backtrack',
            'additional_information'        => 'You must be at least 19 years to attend this show.',
            'date'                          => '2017-11-18',
            'time'                          => '8:00pm',
            'venue'                         => 'The Mosh Pit',
            'venue_address'                 => '123 Fake St.',
            'city'                          => 'Laraville',
            'state'                         => 'ON',
            'zip'                           => '12345',
            'ticket_price'                  => '32.50',
            'ticket_quantity'               => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
            $response->assertRedirect(route('backstage.concerts.index'));

            $this->assertfalse($concert->isPublished());
            $this->assertTrue($concert->user->is($user));

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
            $this->assertEquals(75, $concert->ticket_quantity);
            $this->assertEquals(0, $concert->ticketsRemaining());
        });
    }

    /** @test */
    public function guests_cannot_add_valid_concerts()
    {
        $response = $this->post('backstage/concerts', $this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function title_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['title' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function subtitle_field_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('backstage/concerts', $this->validParams(['subtitle' => '']));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
            $response->assertRedirect(route('backstage.concerts.index'));

            $this->assertTrue($concert->user->is($user));

            $this->assertNull($concert->subtitle);
        });
    }

    /** @test */
    public function additional_information_field_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('backstage/concerts/new')
                                          ->post('backstage/concerts', $this->validParams(['additional_information' => '']));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
            $response->assertRedirect(route('backstage.concerts.index'));

            $this->assertTrue($concert->user->is($user));

            $this->assertNull($concert->additional_information);
        });
    }

    /** @test */
    public function date_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['date' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('date');
    }

    /** @test */
    public function date_must_be_a_valid_date()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['date' => 'invalid-date']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('date');
    }

    /** @test */
    public function time_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['time' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('time');
    }

    /** @test */
    public function time_must_be_a_valid_time()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['time' => 'not-a-valid-time']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('time');
    }

    /** @test */
    public function venue_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['venue' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('venue');
    }

    /** @test */
    public function venue_address_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['venue_address' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('venue_address');
    }

    /** @test */
    public function city_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['city' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('city');
    }

    /** @test */
    public function state_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['state' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('state');
    }

    /** @test */
    public function zip_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['zip' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('zip');
    }

    /** @test */
    public function ticket_price_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['ticket_price' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    public function ticket_price_must_be_numeric()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['ticket_price' => 'thirtytwofifty']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    public function ticket_price_must_be_at_least_5()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['ticket_price' => '4.99']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    public function ticket_quantity_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['ticket_quantity' => '']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_numeric()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['ticket_quantity' => 'seventyfive']));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['ticket_quantity' => 0]));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    public function poster_image_is_uploaded_if_included()
    {
        $this->disableExceptionHandling();

        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 850, 1100);

        $response = $this->actingAs($user)
                         ->post('backstage/concerts', $this->validParams([
                            'poster_image' => $file,
                    ]));
        
        tap(Concert::first(), function ($concert) use ($file) {
            $this->assertNotNull($concert->poster_image_path);
            Storage::disk('s3')->exists($concert->poster_image_path);
            $this->assertFileEquals(
                $file->getPathName(),
                Storage::disk('s3')->path($concert->poster_image_path)
            );
        });
    }

    /** @test */
    public function poster_image_must_be_an_image()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::create('not-a-poster.pdf');

        $response = $this->actingAs(factory(User::class)->create())
                         ->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['poster_image' => $file]));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('poster_image');
    }

    /** @test */
    public function poster_image_must_be_at_least_400_px_wide()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 399, 516);

        $response = $this->actingAs(factory(User::class)->create())
                         ->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['poster_image' => $file]));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('poster_image');
    }

    /** @test */
    public function poster_image_must_have_correct_letter_aspect_ratio()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 851, 1100);

        $response = $this->actingAs(factory(User::class)->create())
                         ->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['poster_image' => $file]));

        $response->assertStatus(302);
        $response->assertRedirect('backstage/concerts/new');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('poster_image');
    }

    /** @test */
    public function poster_image_is_optional()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
                         ->from('backstage/concerts/new')
                         ->post('backstage/concerts', $this->validParams(['poster_image' => null]));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
            $response->assertRedirect(route('backstage.concerts.index'));

            $this->assertTrue($concert->user->is($user));

            $this->assertNull($concert->poster_image_path);
        });
    }
}

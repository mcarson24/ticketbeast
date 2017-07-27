<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertTest extends TestCase
{
	use DatabaseMigrations;

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
}

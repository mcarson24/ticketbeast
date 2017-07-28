<?php

namespace Tests\Feature;

use App\User;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function guests_cannot_view_a_promoters_concert_list()
    {
        $response = $this->get('backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    /** @test */
    public function a_promoter_can_view_a_list_of_only_their_own_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concertA = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertB = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertC = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $concertD = factory(Concert::class)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('backstage/concerts');

        $response->assertStatus(200);
        $this->assertTrue($response->original->getData()['concerts']->contains($concertA));
        $this->assertTrue($response->original->getData()['concerts']->contains($concertB));
        $this->assertFalse($response->original->getData()['concerts']->contains($concertC));
        $this->assertTrue($response->original->getData()['concerts']->contains($concertD));
    }
}

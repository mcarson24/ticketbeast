<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewPublishedConcertOrdersTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_cannot_view_the_orders_of_unpublished_concert()
    {
        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_promoter_cannot_view_the_orders_of_aother_promoters_published_concert()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_guest_cannot_view_the_orders_of_any_published_concerts()
    {
        $concert = \ConcertFactory::createPublished();

        $response = $this->get("backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('login');
    }
}

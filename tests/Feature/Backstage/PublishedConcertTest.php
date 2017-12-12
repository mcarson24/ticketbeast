<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PublishedConcertTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_publish_their_own_concert()
    {
    	$this->withExceptionHandling();
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
        	'user_id' => $user->id,
        	'ticket_quantity' => 3
    	]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', ['concert_id' => $concert->id]);

        $response->assertRedirect('/backstage/concerts');
        tap($concert->fresh(), function($concert) {
	        $this->assertTrue($concert->isPublished());
	        $this->assertEquals(3, $concert->ticketsRemaining());
        });
    }

    /** @test */
    public function a_concert_can_only_be_published_once()
    {
        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished([
        	'user_id' => $user->id,
        	'ticket_quantity' => 3
    	]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', ['concert_id' => $concert->id]);

        $response->assertStatus(422);
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    public function a_promoter_cannot_publish_other_promoters_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
        	'user_id' => $user->id,
        	'ticket_quantity' => 3
    	]);

    	$response = $this->actingAs($otherUser)->post('/backstage/published-concerts', ['concert_id' => $concert->id]);

    	$response->assertStatus(404);
    	tap($concert->fresh(), function($concert) {
    		$this->assertFalse($concert->isPublished());
    		$this->assertEquals(0, $concert->ticketsRemaining());
    	});
    }

    /** @test */
    public function a_guest_cannot_publish_concerts()
    {
        $concert = factory(Concert::class)->states('unpublished')->create([
        	'ticket_quantity' => 3
    	]);

    	$response = $this->post('/backstage/published-concerts', ['concert_id' => $concert->id]);

    	$response->assertRedirect('login');
    	tap($concert->fresh(), function($concert) {
    		$this->assertFalse($concert->isPublished());
    		$this->assertEquals(0, $concert->ticketsRemaining());
    	});
    }

    /** @test */
    public function nonexistent_concerts_cannot_be_published()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('backstage/published-concerts', ['concert_id' => 999]);

        $response->assertStatus(404);
    }
}

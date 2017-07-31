<?php

namespace Tests\Feature;

use App\User;
use App\Concert;
use Tests\TestCase;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase
{
	use DatabaseMigrations;

	public function setUp()
	{
		parent::setUp();

        // Moved this macro into TestCase
		// TestResponse::macro('data', function($key) {
		// 	return $this->original->getData()[$key];
		// });

		Collection::macro('assertContains', function($value) {
			return Assert::assertTrue($this->contains($value), "Failed asserting that the collection contained:\n{$value}");
		});

		Collection::macro('assertNotContains', function($value) {
			return Assert::assertFalse($this->contains($value), "The collection contained an unwanted value:\n{$value}");
		});
	}

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
        $response->data('concerts')->assertContains($concertA);
        $response->data('concerts')->assertContains($concertB);
        $response->data('concerts')->assertContains($concertD);
        $response->data('concerts')->assertNotContains($concertC);
    }
}

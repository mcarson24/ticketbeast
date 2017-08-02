<?php

namespace Tests\Feature;

use App\User;
use App\Concert;
use Tests\TestCase;
use ConcertFactory;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase
{
	use DatabaseMigrations;

	public function setUp()
	{
		parent::setUp();

		Collection::macro('assertContains', function($value) {
			return Assert::assertTrue($this->contains($value), "Failed asserting that the collection contained:\n{$value}");
		});

		Collection::macro('assertNotContains', function($value) {
			return Assert::assertFalse($this->contains($value), "The collection contained an unwanted value:\n{$value}");
		});

        Collection::macro('assertEquals', function($items) {
            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
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

        $publishedConcertA = ConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = ConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertC = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $unpublishedConcertA = ConcertFactory::createUnpublished(['user_id' => $user->id]);
        $unpublishedConcertB = ConcertFactory::createUnpublished(['user_id' => $user->id]);
        $unpublishedConcertC = ConcertFactory::createUnpublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get('backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertB,
        ]);

        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertB,
        ]);
    }
}

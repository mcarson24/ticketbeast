<?php

namespace Tests;

use App\User;
use \Mockery;
use App\Exceptions\Handler;
use PHPUnit\Framework\Assert;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {   
        parent::setUp();

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function($key) {
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function($value) {
            return Assert::assertTrue($this->contains($value), "Failed asserting that the collection contained:\n{$value}");
        });

        EloquentCollection::macro('assertNotContains', function($value) {
            return Assert::assertFalse($this->contains($value), "The collection contained an unwanted value:\n{$value}");
        });

        EloquentCollection::macro('assertEquals', function($items) {
            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

    protected function signIn(User $user = null)
    {
        $this->be($user = $user ?: factory(User::class)->create());

        return $user;
    }
}

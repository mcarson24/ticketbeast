<?php

use App\Concert;

class ConcertFactory
{
	public static function createPublished($overrides = [])
	{
		return factory(Concert::class)->create($overrides)->publish();
	}

	public static function createUnpublished($overrides = [])
	{
		return factory(Concert::class)->states('unpublished')->create($overrides);
	}
}
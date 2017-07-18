<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
	// Must be 24 characters long
	// Will only contain uppercase letters and numbers
	// Cannot contain ambiguous characters
	// EXCLUDE: 1 & I, O & 0
	// ABCDEFGHJKLMNPQRSTUVWXYZ23456789
	// All confirmations numbers must be unique
	// 
	/** @test */
	public function must_be_twenty_four_characters_long()
	{
	    $generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmation_number = $generator->generate();

	    $this->assertEquals(24, strlen($confirmation_number));
	}

	/** @test */
	public function can_only_contain_numbers_and_uppercase_letters()
	{
	    $generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmation_number = $generator->generate();

	    $this->assertRegExp('/^[A-Z0-9]+$/', $confirmation_number);
	}

	/** @test */
	public function cannot_contain_ambiguous_characters()
	{
		$generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmation_number = $generator->generate();

	    $this->assertFalse(strpos($confirmation_number, '1'));
	    $this->assertFalse(strpos($confirmation_number, 'I'));
	    $this->assertFalse(strpos($confirmation_number, '0'));
	    $this->assertFalse(strpos($confirmation_number, 'O'));
	}

	/** @test */
	public function generated_numbers_must_be_unique()
	{
		$generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmation_numbers = array_map(function($i) use ($generator) {
	    	return $generator->generate();
	    }, range(1, 100));
	    
	    $this->assertCount(100, array_unique($confirmation_numbers));
	}
}

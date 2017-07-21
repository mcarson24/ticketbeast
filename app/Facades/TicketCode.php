<?php

namespace App\Facades;

use App\TicketCodeGenerator;
use Illuminate\Support\Facades\Facade;

class TicketCode extends Facade
{
	protected static function getFacadeAccessor()
	{
		return TicketCodeGenerator::class;
	}

	protected static function getMockableClass()
	{
		return static::getFacadeAccessor();
	}
}
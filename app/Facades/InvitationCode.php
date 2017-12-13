<?php

namespace App\Facades;

use App\InvitationCodeGenerator;
use Illuminate\Support\Facades\Facade;

class InvitationCode extends Facade
{
	protected static function getFacadeAccessor()
	{
		return InvitationCodeGenerator::class;
	}
}

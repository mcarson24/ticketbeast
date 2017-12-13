<?php

use App\Invitation;
use App\Facades\InvitationCode;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('invite-promoter {email}', function($email) {
	$invitation = Invitation::create([
		'code'		=> InvitationCode::generate(),
		'email'		=> $email
	]);
})->describe('Invite a new promoter to create a TicketBeast account.');

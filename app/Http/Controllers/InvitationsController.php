<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;

class InvitationsController extends Controller
{
    public function show($code)
    {
    	return view('invitations.show', [
    		'invitation' => Invitation::findByCode($code)
    	]);
    }
}

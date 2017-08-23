<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConcertMessagesController extends Controller
{
    public function create($id)
    {
    	$concert = auth()->user()->concerts()->findOrFail($id);
    	
    	return view('backstage.concert-messages.create', compact('concert'));
    }
}

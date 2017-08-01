<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function create()
    {
    	return view('backstage.concerts.create');
    }

    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Auth::user()->concerts]);
    }

    public function store()
    {
    	$this->validate(request(), [
    		'title' 			=> 'required',
    		'date'				=> 'required|date',
    		'time'				=> 'required|date_format:g:ia',
    		'venue'				=> 'required',
    		'venue_address'		=> 'required',
    		'city'				=> 'required',
    		'state'				=> 'required',
    		'zip'				=> 'required',
    		'ticket_price' 		=> 'required|numeric|min:5',
    		'ticket_quantity'	=> 'required|numeric|min:1'
		]);

    	$concert = auth()->user()->concerts()->create([
    		'title' 					=> request('title'),
    		'subtitle' 					=> request('subtitle'),
    		'date'						=> Carbon::parse(vsprintf('%s %s', [request('date'), request('time')])),
    		'ticket_price'				=> request('ticket_price') * 100,
    		'venue'						=> request('venue'),
    		'venue_address' 			=> request('venue_address'),
    		'city' 						=> request('city'),
    		'state' 					=> request('state'),
    		'zip'	 					=> request('zip'),
    		'additional_information'	=> request('additional_information'),
            'ticket_quantity'           => (int) request('ticket_quantity'),
		])->addTickets(request('ticket_quantity'))->publish();

    	return redirect()->route('concerts.show', $concert);
    }

    public function edit($id)
    {
        $concert = Concert::fromCurrentUser()->findOrFail($id);

        abort_if ($concert->isPublished(), 403);

        return view('backstage.concerts.edit', ['concert' => $concert]);
    }

    public function update($id)
    {
        $concert = Concert::fromCurrentUser()->findOrFail($id);
        
        abort_if ($concert->isPublished(), 403);

        $this->validate(request(), [
            'title'             => 'required',
            'date'              => 'required|date',
            'time'              => 'required|date_format:g:ia',
            'venue'             => 'required',
            'venue_address'     => 'required',
            'city'              => 'required',
            'state'             => 'required',
            'zip'               => 'required',
            'ticket_price'      => 'required|numeric|min:5',
            'ticket_quantity'   => 'required|integer|min:1',
        ]);

        $concert->update([
            'title'                     => request('title'),
            'subtitle'                  => request('subtitle'),
            'additional_information'    => request('additional_information'),
            'date'                      => Carbon::parse(vsprintf('%s %s', [request('date'), request('time')])),
            'venue'                     => request('venue'),
            'venue_address'             => request('venue_address'),
            'city'                      => request('city'),
            'state'                     => request('state'),
            'zip'                       => request('zip'),
            'ticket_price'              => request('ticket_price') * 100,
            'ticket_quantity'           => (int) request('ticket_quantity'),
        ]);;

        return redirect()->route('backstage.concerts.index');
    }
}

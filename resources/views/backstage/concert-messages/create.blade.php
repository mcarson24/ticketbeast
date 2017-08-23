@extends('layouts.backstage')

@section('backstageContent')
@include('backstage._sub-navbar', ['page' => 'message'])
<div class="bg-soft p-xs-y-5">
    <div class="container m-xs-b-4">
        <div class="constrain constrain-lg m-xs-auto">
            <h1 class="text-xl wt-light text-center m-xs-b-4 text-dark">New Message</h1>

            @if (session()->has('flash'))
            <div class="alert alert-success m-xs-b-4">Message sent!</div>
            @endif

            <div class="card p-xs-6">
                <form action="{{ route('backstage.concert-messages.store', $concert) }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="10" required></textarea>
                    </div>
                    <div>
                        <button class="btn btn-primary btn-block text-smooth">Send Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

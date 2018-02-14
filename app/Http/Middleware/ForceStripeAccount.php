<?php

namespace App\Http\Middleware;

class ForceStripeAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (auth()->user()->stripe_account_id === null)
        {
            return redirect()->route('backstage.stripe-connect.connect');
        }

        return $next($request);
    }
}

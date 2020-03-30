<?php

namespace App\Http\Middleware;

use Closure;
use Twilio\Rest\Client;

class MemberOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $twilio = new Client(env('TWILIO_AUTH_SID'), env('TWILIO_AUTH_TOKEN'));

        try {
            $twilio->chat->v2->services(env('TWILIO_CHAT_SERVICE_SID'))
                ->channels('chatroom')
                ->members(auth()->user()->username)
                ->fetch();

            return $next($request);
        } catch (\Twilio\Exceptions\RestException $e) {
            return redirect('/home');
        }
    }
}

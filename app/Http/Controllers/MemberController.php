<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;

class MemberController extends Controller
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(env('TWILIO_AUTH_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function ban($username)
    {
        $this->twilio->chat->v2->services(env('TWILIO_CHAT_SERVICE_SID'))
                                        ->channels('chatroom')
                                        ->members($username)
                                        ->update([
                                            'roleSid' => env('MIX_CHANNEL_BANNED_ROLE_SID')
                                        ]);
        return response()->json([
            'message' => 'Member banned'
        ]);
    }

    public function unban($username)
    {
        $this->twilio->chat->v2->services(env('TWILIO_CHAT_SERVICE_SID'))
                                        ->channels('chatroom')
                                        ->members($username)
                                        ->update([
                                            'roleSid' => env('MIX_CHANNEL_MEMBER_ROLE_SID')
                                        ]);
        return response()->json([
            'message' => 'Member unbanned'
        ]);
    }
}

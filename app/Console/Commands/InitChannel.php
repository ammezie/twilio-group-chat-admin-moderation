<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Client;
use App\User;

class InitChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channel:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);

        $twilio = new Client(env('TWILIO_AUTH_SID'), env('TWILIO_AUTH_TOKEN'));

        $channel = $twilio->chat->v2->services(env('TWILIO_CHAT_SERVICE_SID'))
                        ->channels
                        ->create([
                            'friendlyName' => 'chatroom',
                            'uniqueName' => 'chatroom',
                            'createdBy' => $user->username
                        ]);

        $twilio->chat->v2->services(env('TWILIO_CHAT_SERVICE_SID'))
            ->channels($channel->sid)
            ->members
            ->create($user->username, [
                'roleSid' => env('MIX_CHANNEL_ADMIN_ROLE_SID')
            ]);

        return $this->info('Channel initialized');
    }
}

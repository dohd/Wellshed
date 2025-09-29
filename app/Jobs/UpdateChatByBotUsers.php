<?php

namespace App\Jobs;

use App\Models\lead\OmniChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class UpdateChatByBotUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // fetch bot-users 
        $controller = new \App\Http\Controllers\Focus\omniconvo\OmniController;
        $botUsers = (array) $controller->queryBotUsers($this->data);
        if (!array_filter($botUsers)) $botUsers = [];

        Log::info('Bot users queried: ' . count($botUsers));
        Log::info('Begin chat update using bot users...');

        // update chat attributes using bot-users
        $botUsers = collect($botUsers)->keyBy('fbId');
        $chats = OmniChat::withoutGlobalScopes()
            ->whereNull('user_type')
            ->orWhereNull('phone_no')
            ->orWhereNull('country')
            ->get()
            ->keyBy('fb_id');
        // Log::info('bot user: urban', (array) @$botUsers['254713596615']);
        // Log::info('chat: urban', (array) @$chats['254713596615']);

        $isUpdated = 0;
        foreach ($chats as $fbId => $chat) {
            if (@$botUsers[$fbId]) {
                $params['phone_no'] = $botUsers[$fbId]->phone_number;
                $params['country'] = $botUsers[$fbId]->country;
                // assign user type
                $botUserType = $botUsers[$fbId]->type;
                foreach (['website', 'whatsapp', 'facebook', 'instagram'] as $value) {
                    if (stripos($botUserType, $value) !== false) {
                        $params['user_type'] = $value;
                        break;
                    }
                }
                $dbChat = OmniChat::withoutGlobalScopes()->where('fb_id', $fbId)->first();
                $isUpdated = $dbChat->update($params);
            }
        }

        if ($isUpdated) Log::info('Update Done.');
        else Log::info('No pending update.');
    }
}

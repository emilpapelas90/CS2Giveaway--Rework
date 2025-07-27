<?php

namespace App\Services;
use App\Models\SocialAccounts;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class DiscordService
{
    public function isUserInGuild(SocialAccounts $account, $guildId): bool {
        $response = Http::withToken($account->token)
            ->get("https://discord.com/api/users/@me/guilds");

        if ($response->successful()) {
            $guilds = $response->json();

            foreach ($guilds as $guild) {
                if ($guild['id'] == $guildId) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SocialAccounts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    public function redirect() {
      return Socialite::driver('discord')->scopes(['identify', 'guilds'])->redirect();
    }

    public function callback() {
        $discordUser = Socialite::driver('discord')->stateless()->user();
        $user = Auth::user();

        // Save or update Discord account
        $social = SocialAccounts::updateOrCreate([
            'user_id' => $user->id,
            'provider' => 'discord',
        ], [
            'provider_user_id' => $discordUser->getId(),
            'username' => $discordUser->getNickname(),
            'token' => $discordUser->token,
        ]);

        // Check if user is in the server
        $guilds = Http::withToken($discordUser->token)
            ->get('https://discord.com/api/users/@me/guilds')
            ->json();

        if (!is_array($guilds)) {
            return redirect()->route('dashboard')->with('error', 'Failed to fetch guilds from Discord.');
        }

        $guildId = (string) env('DISCORD_GUILD_ID');

        $inServer = collect($guilds)->contains(fn($g) => isset($g['id']) && (string) $g['id'] === $guildId);

        $social->update(['in_server' => $inServer]);

        if (!$inServer) {
            $invite = Http::withToken(env('DISCORD_BOT_TOKEN'))
                ->post("https://discord.com/api/v10/channels/" . env('DISCORD_INVITE_CHANNEL_ID') . "/invites", [
                    'max_uses' => 1,
                    'unique' => true,
                ])
                ->json();

            if (isset($invite['code'])) {
                return redirect("https://discord.gg/{$invite['code']}");
            }

            return redirect()->route('dashboard')->with('error', 'Failed to create Discord invite.');
        }

      return redirect()->route('dashboard')->with('success', 'Discord account connected!');
    }
}

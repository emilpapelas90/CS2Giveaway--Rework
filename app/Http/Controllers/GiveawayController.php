<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Giveaway;
use App\Models\GiveawayUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\DiscordService;
use Illuminate\Http\Request;

class GiveawayController extends Controller
{

    public function giveaways() {
      $giveaways = Giveaway::where('is_active', 1)->get()->filter(function ($giveaway) {
        return Carbon::parse($giveaway->end_time)->isFuture();
      });
      return Inertia::render('user/Giveaways', ['giveaways' => $giveaways]);
    }
    // public function giveaways() {
    //   $giveaways = Giveaway::where('is_active', 1)->get()->filter(function ($giveaway) {
    //     return Carbon::parse($giveaway->end_time)->isFuture();
    //   });
    //   return Inertia::render('user/Giveaways', ['giveaways' => $giveaways]);
    // }

    public function join($id, DiscordService $discordService) {
      
      $giveaway = Giveaway::with('users:id,name')->findOrFail($id);
      $requirements = $giveaway->requirements;
      $user = auth()->user();

      $discordAccount = $user->socialAccounts()->where('provider', 'discord')->first();

      //dd($discordAccount);


      foreach ($requirements as &$req) {
        if ($req['type'] === 'discord') {
            $req['in_server'] = false;

            if ($discordAccount) {
                $inServer = $discordService->isUserInGuild($discordAccount, $req['guild_id']);

                // Optionally update the DB field
                $discordAccount->update(['in_server' => $inServer]);

                $req['in_server'] = $inServer;
            }
        }
      }

      $requirements[] = [
        'type' => 'email',
        'label' => 'Confirm your email address',
        'confirmed' => !is_null($user->email_verified_at),
      ];

      
      $entered = $giveaway->users()->where('user_id', $user->id)->exists();;

      return Inertia::render('user/GiveawayJoin2', [
        'giveaway' => [
          'id' => $giveaway->id,
          'skin_name' => $giveaway->skin_name,
          'image' => $giveaway->image,
          'value' => $giveaway->value,
          'rarity' => $giveaway->rarity,
          'entries' => $giveaway->entries,
          'max_entries' => $giveaway->max_entries,
          'requirements' => $requirements,
          'created_at' => $giveaway->created_at,
          'end_time' => $giveaway->end_time,
          'entered' => $entered, 
          'type' => $giveaway->type,
          'is_active' => $giveaway->is_active,
          'participants' => $giveaway->users->map(function ($user) {
            return [
              'id' => $user->id,
              'name' => $user->name,
            ];
          }),
        ],

        'requirements' => $requirements,
      ]);
    }

    public function enter(Giveaway $giveaway) {
      $user = auth()->user();


      if (now()->greaterThan($giveaway->end_time)) {
        return back()->withErrors(['giveaway' => 'This giveaway has already ended.']);
      }

      if (is_null($user->email_verified_at)) {
        throw ValidationException::withMessages([
            'email' => 'Please verify your email before entering the giveaway.',
        ]);
      }

      $requirements = $giveaway->requirements;


      foreach ($requirements as $requirement) {
        if ($requirement['type'] === 'discord') {
            // Extract the server (guild) ID from the invite link
            $inviteCode = str_replace('https://discord.gg/', '', $requirement['invite']);

            $inviteResponse = Http::get("https://discord.com/api/v10/invites/{$inviteCode}?with_counts=true");

            if (!$inviteResponse->ok()) {
                throw ValidationException::withMessages([
                    'requirement' => 'Failed to verify Discord server requirement.',
                ]);
            }

            $guildId = $inviteResponse->json('guild.id');

        }
      }

      // Prevent multiple entries
      if ($giveaway->hasUserEntered($user->id)) {
        throw ValidationException::withMessages([
            'giveaway' => 'You have already entered this giveaway.',
        ]);
      }

      $giveaway->users()->attach($user->id, [
        'entered_at' => now(),
      ]);

      $giveaway->increment('entries');

     return back()->with('success', 'You have successfully entered the giveaway!');
    }
}

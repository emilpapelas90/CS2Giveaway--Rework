<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Giveaway;
use App\Models\GiveawayWinners;
use App\Models\GiveawayUser;
use App\Events\GiveawayUpdated;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\DiscordService;
use Illuminate\Http\Request;

use App\Jobs\ProcessGiveawaysJob;

class GiveawayController extends Controller
{


    public function test() {
        return Inertia::render('user/test');
    }

    public function giveaways() {
      //$giveaways = Giveaway::where('is_active', 1)->where(function ($q) { $q->whereNull('start_time'); })->get();

      $giveaways = Giveaway::where('is_active', 0)->get();

      return Inertia::render('user/Giveaways', ['giveaways' => $giveaways]);
    }

    public function simulate($giveaway) {
     abort_unless(app()->environment(['local', 'staging']), 403);
     dispatch_sync(new ProcessGiveawaysJob($giveaway));

    }

    public function join($id) {

      //$giveaway = Giveaway::with(['winner.user', 'participants'])->findOrFail($id);
      $giveaway = Giveaway::with([
        'winner.user',
        'participants' => function($q) {
            $q->withPivot('entered_at'); // make sure pivot data is loaded
      }])->findOrFail($id);
      $user = auth()->user();

      // Check if user already joined
      $entered = $giveaway->participants->pluck('id')->contains($user->id);

      $requirements = $giveaway->requirements;

    //   $participants = $giveaway->participants
    //     ->sortByDesc(fn($user) => strtotime($user->pivot->entered_at ?? now()))
    //     ->values() // reindex the collection so JSON preserves order
    //     ->map(fn($user) => [
    //         'id' => $user->id,
    //         'name' => $user->name,
    //         'entered_at' => $user->pivot->entered_at,
    //   ]);

      $participants = $giveaway->participants->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'entered_at' => $user->pivot->entered_at,
      ]);

      //dd($participants);

      return Inertia::render('user/GiveawayJoin2', [
          'giveaway' => [
              'id' => $giveaway->id,
              'skin_name' => $giveaway->skin_name,
              'image' => $giveaway->image,
              'value' => $giveaway->value,
              'rarity' => $giveaway->rarity,
              'entries' => $giveaway->entries,
              'max_entries' => $giveaway->max_entries,
              'start_time' => $giveaway->start_time,
              'duration_minutes' => $giveaway->duration_minutes,
              'min_entries' => $giveaway->min_entries,
              'created_at' => $giveaway->created_at,
              'entered' => $entered,
              'type' => $giveaway->type,
              'is_active' => $giveaway->is_active,
              'winner' => $giveaway->winner ? [
                  'id' => $giveaway->winner->id,
                  'user' => [
                      'id' => $giveaway->winner->user->id,
                      'name' => $giveaway->winner->user->name,
                  ],
              ] : null,
              'participants' => $participants
          ],
          'requirements' => $requirements,
      ]);

    }

    public function enter(Giveaway $giveaway) {

      $user = auth()->user();

      // Check if user already joined
      if ($giveaway->participants()->where('user_id', $user->id)->exists()) {
          return back()->with('error', 'You have already joined this giveaway.');
      }

      // Attach user
      $giveaway->users()->attach($user->id, [
        'entered_at' => now(),
      ]);

      // Update entries count
      $giveaway->entries = $giveaway->participants()->count();

      // Auto-start if min entries reached
      if (!$giveaway->start_time && $giveaway->entries >= $giveaway->min_entries) {
          $giveaway->start_time = now();
          //$giveaway->is_active = 1;
      }


      $giveaway->save();
      
      //dd(new GiveawayUpdated($giveaway));
      event(new GiveawayUpdated($giveaway));
     return back()->with('success', 'You have successfully entered the giveaway!');
    }


    // public function selectWinner($id) {
    //   $giveaway = Giveaway::with('participants')->findOrFail($id);

    //   // Make sure giveaway is ended
    //   if (now()->lessThan($giveaway->end_time)) {
    //       return back()->with('error', 'Giveaway has not ended yet.');
    //   }

    //   // Check if winner already selected
    //   if ($giveaway->winner) {
    //       return back()->with('error', 'Winner already selected.');
    //   }
      
    //   $participants = $giveaway->participants;

    //   if ($participants->isEmpty()) {
    //       return back()->with('error', 'No participants to select a winner.');
    //   }

    //   $winner = $participants->random();

    //   $prizeInfo = [
    //     'skin_name' => $giveaway->skin_name,
    //     'image' => $giveaway->image, // assuming you have this
    //     'value' => $giveaway->value ?? null,
    //     'rarity' => $giveaway->rarity ?? null,
    //   ];
      
    //   GiveawayWinners::create([
    //     'giveaway_id' => $giveaway->id,
    //     'user_id' => $winner->id,
    //     'prize' => $prizeInfo,
    //   ]);

    //   // Optionally increment giveaways_won
    //   $winner->increment('giveaways_won');
    //   $giveaway->delete();

    //   return back()->with('success', "🎉 Winner selected: {$winner->name}");
    // }
}

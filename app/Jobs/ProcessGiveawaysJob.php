<?php

namespace App\Jobs;

use App\Models\Giveaway;
use App\Models\GiveawayUser;
use App\Models\GiveawayWinners;
use App\Models\Items;

use App\Events\GiveawayEnded;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessGiveawaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('Giveaway worker running', ['time' => now()]);

        DB::transaction(function () {
            $this->processEndedGiveaways();
            $this->ensureActiveGiveaway();
        });
    }

    private function processEndedGiveaways(): void
    {
        $ended = Giveaway::where('is_active', 1)
            ->whereRaw(
                'DATE_ADD(start_time, INTERVAL duration_minutes MINUTE) <= ?',
                [now()]
            )
            ->lockForUpdate()
            ->get();

        if ($ended->isEmpty()) {
            Log::info('No ended giveaways found');
            return;
        }

        foreach ($ended as $giveaway) {
            Log::info("Processing giveaway #{$giveaway->id}");

            $entries = GiveawayUser::where('giveaway_id', $giveaway->id)
                ->whereHas('user')
                ->with('user')
                ->get();

            if ($entries->isEmpty()) {
                Log::warning("Giveaway {$giveaway->id} has no valid entries");
                $giveaway->update(['is_active' => 0]);
                continue;
            }

           

            foreach ($entries as $entry) {
                $entry->fair_roll = $this->generateRoll(

                    //dd($entry->nonce),

                    $giveaway->server_seed,

                     
                    $entry->user->client_seed,
                   (int) $entry->nonce
                );
            }

            $winner = $entries->sortByDesc('fair_roll')->first();
            //dd($winner);

            if (GiveawayWinners::where('giveaway_id', $giveaway->id)->exists()) {
                Log::warning("Giveaway {$giveaway->id} already processed");
                continue;
            }
            
            GiveawayWinners::create([
                'giveaway_id' => $giveaway->id,
                'user_id' => $winner->user_id,
                'fair_roll' => $winner->fair_roll,
            ]);

            $giveaway->update([
                'revealed_server_seed' => $giveaway->server_seed,
                'is_active' => 0,
            ]);

            event(new GiveawayEnded(
                $giveaway,
                [
                    'user_id' => $winner->user_id,
                    'name'    => $winner->user->name,
                    'roll'    => $winner->fair_roll,
                ]
            ));

            //event(new GiveawayEnded($giveaway, $winner));

            Log::info("Giveaway #{$giveaway->id} ended. Winner: {$winner->user_id}");
        }
    }

    private function ensureActiveGiveaway(): void
    {
        $active = Giveaway::where('is_active', 1)
            ->whereRaw(
                'DATE_ADD(start_time, INTERVAL duration_minutes MINUTE) > ?',
                [now()]
            )
            ->first();

        if ($active) {
            Log::info("Active giveaway exists (#{$active->id})");
            return;
        }

        $item = Items::whereBetween('price', [0.50, 1.00])
            ->inRandomOrder()
            ->first();

        if (!$item) {
            Log::error('No items available for giveaway');
            return;
        }

        $serverSeed = bin2hex(random_bytes(16));

        $new = Giveaway::create([
            'skin_name'           => $item->name,
            'image'               => $item->image,
            'value'               => $item->price,
            'rarity'              => $item->color,
            'duration_minutes'    => 60,
            'is_active'           => 1,
            'server_seed'         => $serverSeed,
            'server_seed_hashed'  => hash('sha256', $serverSeed),
        ]);

        Log::info("New giveaway created (#{$new->id})");
    }

    private function generateRoll(string $serverSeed, string $clientSeed, int $nonce): int
    {
        $hash = hash_hmac('sha256', "{$clientSeed}-{$nonce}", $serverSeed);
        return hexdec(substr($hash, 0, 8)) % 1_000_000;
    }
}
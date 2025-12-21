<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Giveaway;
use App\Models\GiveawayUser;
use App\Models\Items;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessGiveaways extends Command
{
    protected $signature = 'giveaways:process';
    protected $description = 'Process ended giveaways and start new ones automatically';

    public function handle()
    {
        $this->info("Worker running at " . now());

        // Process ended giveaways
        $this->processEndedGiveaways();

        // Ensure a giveaway is active
        $this->ensureActiveGiveaway();
    }


    private function processEndedGiveaways()
    {
        $ended = Giveaway::where('is_active', 1)
         ->whereRaw("DATE_ADD(start_time, INTERVAL duration_minutes MINUTE) <= ?", [now()])->get();

        if ($ended->isEmpty()) {
            $this->info("No ended giveaways to process.");
            return;
        }

        foreach ($ended as $giveaway) {
            $this->info("Processing giveaway #{$giveaway->id}...");

            // Get all entries that belong to users that actually exist
            $entries = GiveawayUser::where('giveaway_id', $giveaway->id)
                ->whereHas('user')
                ->with('user')
                ->get();

            if ($entries->isEmpty()) {
                $this->warn("Giveaway {$giveaway->id} has no valid entries");
                $giveaway->is_active = 0;
                $giveaway->save();
                continue;
            }

            // Fair roll: hash → random number
            foreach ($entries as $entry) {
                $entry->fair_roll = $this->generateRoll(
                    $giveaway->server_seed,
                    $entry->user->client_seed,
                    $entry->nonce
                );
            }

            // Pick winner by highest roll
            $winner = $entries->sortByDesc('fair_roll')->first();

            if (!$winner) {
                $this->error("Something went wrong — no winner could be selected.");
                continue;
            }

            $this->info("Winner user_id = {$winner->user_id}");

            // Update giveaway
            //dd($giveaway);
            //$giveaway->winner_user_id = $winner->user_id;
            $giveaway->revealed_server_seed = $giveaway->server_seed;
            $giveaway->is_active = 0;
            $giveaway->save();

            $this->info("Giveaway #{$giveaway->id} ended successfully.");
        }
    }


    private function ensureActiveGiveaway() {
        $active = Giveaway::where('is_active', 1)
         ->whereRaw("DATE_ADD(start_time, INTERVAL duration_minutes MINUTE) > ?", [now()])->first();

        if ($active) {
            $this->info("Giveaway #{$active->id} is currently active. No new giveaway needed.");
            return;
        }

        $this->info("No active giveaway found. Creating a new one...");

        // Select random item between $0.50 and $1.00
        $item = Items::whereBetween('price', [0.50, 1.00])
            ->inRandomOrder()
            ->first();

        if (!$item) {
            $this->error("No items found within price range $0.50 – $1.00.");
            return;
        }

        //dd($item);

        $serverSeed = bin2hex(random_bytes(16));

        // Create new giveaway
        $new = Giveaway::create([
            'skin_name'  => $item->name,
            'image' => $item->image,
            'value' => $item->price,
            'rarity' => $item->color,
            'is_active'  => 1,
            'server_seed' => $serverSeed,
            'server_seed_hashed' => hash('sha256', $serverSeed),
        ]);

        $this->info("New giveaway created (#{$new->id}).");
    }


    private function generateRoll($serverSeed, $clientSeed, $nonce) {
        $hash = hash_hmac('sha256', $clientSeed . '-' . $nonce, $serverSeed);
        return hexdec(substr($hash, 0, 8)) % 1000000;
    }
}

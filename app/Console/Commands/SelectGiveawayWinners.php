<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Giveaway;
use App\Models\GiveawayWinners;

class SelectGiveawayWinners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'giveaway:select-winners {id : Select a specific giveaway by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Select winners for giveaways that have ended and have no winner yet';

    /**
     * Execute the console command.
     */
    public function handle() {

      $id = $this->argument('id');

    
      if (GiveawayWinners::where('giveaway_id', $id)->exists()) {
        $this->error("❌ A winner has already been selected for giveaway ID: $id");
        return 1; // non-zero exit code means error
      }

      $giveaway = Giveaway::with('participants')->find($id);

        if (!$giveaway) {
            $this->error("Giveaway with ID {$id} not found.");
            return 1;
        }

        if (now()->lt($giveaway->end_time)) {
            $this->error("Giveaway {$id} has not ended yet.");
            return 1;
        }

        $participants = $giveaway->participants;

        if ($participants->isEmpty()) {
            $this->error("Giveaway {$id} has no participants.");
            return 1;
        }

        $winner = $participants->random();

        GiveawayWinners::create([
            'giveaway_id' => $giveaway->id,
            'user_id' => $winner->id,
            'prize' => json_encode([
                'skin_name' => $giveaway->skin_name,
                'image' => $giveaway->image,
                'value' => $giveaway->value,
                'rarity' => $giveaway->rarity,
            ]),
        ]);


        $winner->increment('giveaways_won');
        $giveaway->delete();
        
        $this->info("🎉 Winner selected for Giveaway {$id}: {$winner->name}");

        return 0;
    }
}

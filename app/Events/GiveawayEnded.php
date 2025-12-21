<?php

namespace App\Events;

use App\Models\Giveaway;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class GiveawayEnded implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public Giveaway $giveaway,
        public array $winner
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('giveaway.' . $this->giveaway->id);
    }

    public function broadcastAs()
    {
        return 'GiveawayEnded';
    }

    public function broadcastWith()
    {
        return [
            'giveaway_id' => $this->giveaway->id,
            'winner' => $this->winner,
            'revealed_server_seed' => $this->giveaway->revealed_server_seed,
        ];
    }
}

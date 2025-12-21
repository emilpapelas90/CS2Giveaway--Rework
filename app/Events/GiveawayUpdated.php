<?php

namespace App\Events;

use App\Models\Giveaway;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GiveawayUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $giveaway;

    /**
     * Create a new event instance.
     */
    public function __construct(Giveaway $giveaway)
    {
        Log::info('GiveawayUpdated event fired for giveaway ID: ' . $giveaway->id);
        $this->giveaway = $giveaway;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): PrivateChannel {
       // Log::info("broadcastOn() called for giveaway ID {$this->giveaway->id}");
        return new PrivateChannel('giveaway.' . $this->giveaway->id);
    }

    public function broadcastAs() {
      return 'GiveawayUpdated';
    }

    public function broadcastWith() {    
        return [
          'giveaway' => [
            'id' => $this->giveaway->id,
            'entries' => $this->giveaway->entries,
            'start_time' => $this->giveaway->start_time?->toDateTimeString(),
            'duration_minutes' => $this->giveaway->duration_minutes,
            'participants' => $this->giveaway->participants->map(fn($p)=>[
              'id'=>$p->id,'name'=>$p->name,
              'entered_at'=>$p->pivot->created_at?->toDateTimeString(),
              'ticketCount'=>1
            ])->toArray(),
          ]
        ];
    }
}

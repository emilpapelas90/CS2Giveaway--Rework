<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Giveaway;

class GiveawayWinners extends Model
{
    protected $fillable = ['giveaway_id', 'user_id', 'fair_roll', 'prize'];

    public function giveaway() {
        return $this->belongsTo(Giveaway::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}

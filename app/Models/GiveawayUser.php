<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiveawayUser extends Model
{
    protected $fillable = ['giveaway_id', 'user_id', 'entered_at'];
}

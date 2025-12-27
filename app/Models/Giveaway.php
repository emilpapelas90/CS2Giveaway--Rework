<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GiveawayUser;
use App\Models\GiveawayWinners;

class Giveaway extends Model
{
    protected $fillable = [
        'skin_name',
        'image',
        'value',
        'rarity',
        'entries',
        'start_time',
        'max_entries',
        'duration_minutes',
        'min_entries',
        'type',
        'server_seed',
        'server_seed_hashed',
        'revealed_server_seed',
        'is_active',
    ];

    protected $casts = [
      'start_time' => 'datetime',
    ];

    protected $appends = ['entered'];


    public function winner() {
      return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function entries() {
      return $this->hasMany(GiveawayUser::class, 'giveaway_id');
    }

    public function participants() {
      return $this->belongsToMany(User::class, 'giveaway_user')->withPivot('entered_at');
    }

    public function getEnteredAttribute() {
      return $this->users()->where('user_id', auth()->id())->exists();
    }

    public function users() {
      return $this->belongsToMany(User::class)->withPivot('entered_at')->withTimestamps();
    }

    public function hasUserEntered($userId): bool{
      return $this->users()->where('user_id', $userId)->exists();
    }
}

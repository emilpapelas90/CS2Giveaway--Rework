<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Giveaway extends Model
{
    protected $fillable = [
        'skin_name',
        'image',
        'value',
        'rarity',
        'entries',
        'max_entries',
        'requirements',
        'end_time',
        'type',
        'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        //'end_time' => 'datetime',
    ];

    protected $appends = ['entered'];

    public function getEnteredAttribute() {
      return $this->users()->where('user_id', auth()->id())->exists();
    }

    public function users() {
      return $this->belongsToMany(User::class)->withTimestamps()->withPivot('entered_at');
    }

    public function hasUserEntered($userId): bool{
      return $this->users()->where('user_id', $userId)->exists();
    }
}

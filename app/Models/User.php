<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Giveaway;
use App\Models\GiveawayWinners;
use App\Models\SocialAccounts;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'client_seed',
        'giveaways_won',
        'is_admin',
        'is_blocked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wins() {
      return $this->hasMany(GiveawayWinner::class);
    }

    public function giveaways() {
      return $this->belongsToMany(Giveaway::class)->withTimestamps()->withPivot('entered_at');
    }

    public function socialAccounts() {
      return $this->hasMany(SocialAccounts::class);
    }

    public function discordAccount() {
      return $this->hasOne(SocialAccounts::class)->where('provider', 'discord');
    }
}

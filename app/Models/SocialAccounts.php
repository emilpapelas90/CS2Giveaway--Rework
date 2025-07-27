<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SocialAccounts extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'username',
        'token',
        'refresh_token',
        'in_server',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

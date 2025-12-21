<?php

namespace App\Services;

class ProvablyFairService
{
    public static function generateServerSeed()
    {
        return bin2hex(random_bytes(32)); // 64 chars
    }

    public static function hashServerSeed($seed)
    {
        return hash('sha256', $seed);
    }

    public static function generateRoll($serverSeed, $clientSeed, $nonce)
    {
        $hash = hash('sha256', $serverSeed.$clientSeed.$nonce);

        // convert first 8 chars → integer
        return hexdec(substr($hash, 0, 8));
    }
}
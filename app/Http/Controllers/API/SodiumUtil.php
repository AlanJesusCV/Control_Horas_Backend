<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Support\Facades\Log;
use SodiumException;

class SodiumUtil
{
    private $key;

    public function __construct()
    {
        $this->key = env('ENCRYPTION_KEY');
    }

    public function encryptData($data)
    {
        try {
            $encrypted = openssl_encrypt($data, 'AES-128-ECB', $this->key);
            return $encrypted;
        } catch (Exception $e) {
            Log::error('Error al cifrar datos: ' . $e->getMessage());
            return null;
        }
    }

    public function decryptData($encryptedData)
    {
        try {
            $decrypted = openssl_decrypt($encryptedData, 'AES-128-ECB', $this->key);
            return $decrypted;
        } catch (Exception $e) {
            Log::error('Error al desencriptar datos: ' . $e->getMessage());
            return null;
        }
    }
}

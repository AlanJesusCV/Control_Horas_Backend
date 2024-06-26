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

    public static function encryptData($data)
    {
        try {
            $encrypted = openssl_encrypt($data, 'AES-128-ECB', env('ENCRYPTION_KEY'));
            return trim($encrypted);
        } catch (Exception $e) {
            Log::error('Error al cifrar datos: ' . $e->getMessage());
            return null;
        }
    }

    public static function decryptData($encryptedData)
    {
        try {
            $decrypted = openssl_decrypt($encryptedData, 'AES-128-ECB', 'r:PAu>z}|9c[5$Wd6Xk+$XE)[hB>2W7');
            return $decrypted;
        } catch (Exception $e) {
            Log::error('Error al desencriptar datos: ' . $e->getMessage());
            return null;
        }
    }
}

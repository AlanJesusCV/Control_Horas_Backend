<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Log;
use SodiumException;

class SodiumUtil
{
    // Clave secreta constante (asegúrate de cambiarla en producción)
    const SECRET_KEY = 'RXJyb3IgZW4gbGEgY29uZXhpb24gZGlu';

    public static function encryptData($data)
    {
        try {
            // Verificar la longitud de la clave
            if (mb_strlen(self::SECRET_KEY, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                throw new SodiumException('Longitud de clave incorrecta');
            }

            // Generar un nonce aleatorio
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

            // Cifrar los datos
            $encryptedData = sodium_crypto_secretbox($data, $nonce, self::SECRET_KEY);

            // Combinar el nonce y los datos cifrados
            $combinedData = $nonce . $encryptedData;

            // Reemplazar el carácter "/" en la codificación base64
            $encodedData = str_replace('/', '_', base64_encode($combinedData));

            // Devolver los datos cifrados
            return $encodedData;
        } catch (SodiumException $e) {
            // Manejar errores de cifrado (puedes registrarlos o lanzar una excepción)
            Log::error('Error al cifrar datos: ' . $e->getMessage());
            return null;
        }
    }

    public static function decryptData($encryptedData)
    {
        try {
            // Revertir el reemplazo del carácter "/" en la codificación base64
            $combinedData = base64_decode(str_replace('_', '/', $encryptedData));

            // Extraer el nonce y los datos cifrados
            $nonce = mb_substr($combinedData, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
            $encryptedData = mb_substr($combinedData, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

            // Verificar la longitud de la clave
            if (mb_strlen(self::SECRET_KEY, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                throw new SodiumException('Longitud de clave incorrecta');
            }

            // Desencriptar los datos
            $decryptedData = sodium_crypto_secretbox_open($encryptedData, $nonce, self::SECRET_KEY);

            // Devolver los datos desencriptados
            return $decryptedData;
        } catch (SodiumException $e) {
            // Manejar errores de desencriptación (puedes registrarlos o lanzar una excepción)
            Log::error('Error al desencriptar datos: ' . $e->getMessage());
            return null;
        }
    }
}

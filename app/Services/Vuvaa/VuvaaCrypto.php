<?php

namespace App\Services\Vuvaa;

class VuvaaCrypto
{
    public function __construct(
        private readonly string $key,
        private readonly string $iv,
    ) {
    }

    public function encryptToBase64(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode payload to JSON.');
        }

        $cipherText = openssl_encrypt(
            $json,
            'AES-256-CBC',
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv
        );

        if ($cipherText === false) {
            throw new \RuntimeException('Failed to encrypt payload.');
        }

        return base64_encode($cipherText);
    }

    public function decryptBase64ToArray(string $payloadB64): array
    {
        $cipherText = base64_decode($payloadB64, true);
        if ($cipherText === false) {
            throw new \RuntimeException('Failed to base64 decode payload.');
        }

        $plain = openssl_decrypt(
            $cipherText,
            'AES-256-CBC',
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv
        );

        if ($plain === false) {
            throw new \RuntimeException('Failed to decrypt payload.');
        }

        $data = json_decode(trim($plain), true);
        if (!is_array($data)) {
            throw new \RuntimeException('Decrypted payload is not valid JSON.');
        }

        return $data;
    }
}

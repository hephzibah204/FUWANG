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
            $this->normalizedKey(),
            OPENSSL_RAW_DATA,
            $this->normalizedIv(),
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
            $this->normalizedKey(),
            OPENSSL_RAW_DATA,
            $this->normalizedIv(),
        );

        if ($plain === false) {
            throw new \RuntimeException('Failed to decrypt payload.');
        }

        $data = json_decode($plain, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Decrypted payload is not valid JSON.');
        }

        return $data;
    }

    private function normalizedKey(): string
    {
        $key = $this->key;
        if (strlen($key) === 32) {
            return $key;
        }

        return hash('sha256', $key, true);
    }

    private function normalizedIv(): string
    {
        $iv = $this->iv;
        if (strlen($iv) === 16) {
            return $iv;
        }

        if (strlen($iv) > 16) {
            return substr($iv, 0, 16);
        }

        return str_pad($iv, 16, "\0");
    }
}


<?php
namespace Tests\Unit;

use App\Services\Vuvaa\VuvaaCrypto;
use Tests\TestCase;

class VuvaaCryptoTest extends TestCase
{
    public function test_encrypt_decrypt_roundtrip()
    {
        $key = '12345678901234567890123456789012';  // 32 bytes
        $iv = '1234567890123456';                   // 16 bytes
        
        $crypto = new VuvaaCrypto($key, $iv);
        
        $plainData = [
            'username' => 'testuser',
            'nin' => '12345678901',
            'reference_id' => 'REF20260407123456'
        ];
        
        // Encrypt to Base64
        $encrypted = $crypto->encryptToBase64($plainData);
        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);
        
        // Decrypt back
        $decrypted = $crypto->decryptBase64ToArray($encrypted);
        $this->assertEquals($plainData, $decrypted);
    }
    
    public function test_encrypt_produces_valid_base64()
    {
        $key = '12345678901234567890123456789012';
        $iv = '1234567890123456';
        $crypto = new VuvaaCrypto($key, $iv);
        
        $data = ['username' => 'testuser'];
        $encrypted = $crypto->encryptToBase64($data);
        
        // Verify it's valid Base64
        $this->assertFalse(base64_decode($encrypted, true) === false);
    }
}

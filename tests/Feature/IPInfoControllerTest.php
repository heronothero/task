<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class IPInfoControllerTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function testIpAddressRetrievalAndSave()
    {
        $ip = '8.8.8.8';
        
        $response = $this->get("/ip/{$ip}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('sessions', ['ip_address' => $ip]);
    }
    public function testIpAddressRetrievalFromDatabase()
    {
        $ip = '8.8.8.8';
        DB::table('ip_addresses')->insert(['ip_address' => $ip]);
        $response = $this->get("/ip/{$ip}");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'ip' => $ip,
            'country' => 'United States',
        ]);
    }
    public function testRateLimitHandling()
    {
        for ($i = 0; $i < 50; $i++) {
            DB::table('sessions')->insert(['ip_address' => 'ip'.$i]);
        }
        $newIp = '10.10.10.10';
        $response = $this->get("/ip/{$newIp}");
        $response->assertStatus(429);
    }
}

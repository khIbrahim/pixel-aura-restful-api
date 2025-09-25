<?php

namespace Tests\Feature;

use App\Enum\StoreMemberRole;
use App\Models\V1\Device;
use Laravel\Sanctum\HasApiTokens;
use Tests\TestCase;

class CreateStoreMemberTest extends TestCase
{
    use HasApiTokens;

    public function test_it_can_create_store_member()
    {
        $device = Device::query()->whereKey(108)->first();
        $this->actingAs($device);
        $token = "20|mDI3Uoe5GPHKtvSbp0834c2TIzEH56igQKFoFignfa1b3c0e";
        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->post("/api/v1/stores/108/members", [
            'name' => 'Tarek',
            'role' => StoreMemberRole::Cashier->value,
            'pin'  => '1234',
            'meta' => [
                'phone' => '1234567890',
                'email' => 'tarek76khaled@gmail.com'
            ]
        ])->assertStatus(201);

        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'store_id',
                'user_id',
                'name',
                'role',
                'is_active',
                'meta',
                'created_at',
                'updated_at',
            ]
        ]);

        $this->assertDatabaseHas('store_members', [
            'store_id' => 108,
            'name'     => 'Tarek',
            'role'     => StoreMemberRole::Cashier,
            'is_active'=> true,
        ]);
    }
}

<?php

namespace Tests\Feature\V1;

use App\Enum\StoreMemberRole;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Services\V1\StoreMember\StoreMemberCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreMemberCodeTest extends TestCase
{
    use RefreshDatabase;

    private StoreMemberCodeService $codeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codeService = app(StoreMemberCodeService::class);
    }

    public function test_generates_correct_code_format(): void
    {
        $store = Store::factory()->create();

        $code = $this->codeService->generateUniqueCode($store, StoreMemberRole::Cashier);

        $this->assertMatchesRegularExpression('/^EMP-\d{3}$/', $code);
    }

    public function test_generates_sequential_codes_for_same_role(): void
    {
        $store = Store::factory()->create();

        $code1 = $this->codeService->generateUniqueCode($store, StoreMemberRole::Cashier);
        $code2 = $this->codeService->generateUniqueCode($store, StoreMemberRole::Cashier);
        $code3 = $this->codeService->generateUniqueCode($store, StoreMemberRole::Cashier);

        $this->assertEquals('EMP-001', $code1);
        $this->assertEquals('EMP-002', $code2);
        $this->assertEquals('EMP-003', $code3);
    }

    public function test_generates_different_codes_for_different_roles(): void
    {
        $store = Store::factory()->create();

        $ownerCode = $this->codeService->generateUniqueCode($store, StoreMemberRole::Owner);
        $managerCode = $this->codeService->generateUniqueCode($store, StoreMemberRole::Manager);
        $cashierCode = $this->codeService->generateUniqueCode($store, StoreMemberRole::Cashier);
        $kitchenCode = $this->codeService->generateUniqueCode($store, StoreMemberRole::Kitchen);

        $this->assertStringStartsWith('OWN-', $ownerCode);
        $this->assertStringStartsWith('MGR-', $managerCode);
        $this->assertStringStartsWith('EMP-', $cashierCode);
        $this->assertStringStartsWith('KIT-', $kitchenCode);
    }

    public function test_codes_are_unique_across_stores(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        $code1 = $this->codeService->generateUniqueCode($store1, StoreMemberRole::Cashier);
        $code2 = $this->codeService->generateUniqueCode($store2, StoreMemberRole::Cashier);

        $this->assertEquals('EMP-001', $code1);
        $this->assertEquals('EMP-001', $code2); // Même numéro car stores différents
    }

    public function test_creates_store_member_with_auto_generated_code(): void
    {
        $store = Store::factory()->create();

        $storeMember = StoreMember::factory()->create([
            'store_id' => $store->id,
            'role' => StoreMemberRole::Cashier,
        ]);

        $this->assertNotNull($storeMember->code);
        $this->assertMatchesRegularExpression('/^EMP-\d{3}$/', $storeMember->code);
    }

    public function test_validates_code_format(): void
    {
        $this->assertTrue($this->codeService->validateCodeFormat('EMP-001'));
        $this->assertTrue($this->codeService->validateCodeFormat('OWN-123'));
        $this->assertTrue($this->codeService->validateCodeFormat('MGR-999'));
        $this->assertTrue($this->codeService->validateCodeFormat('KIT-050'));

        $this->assertFalse($this->codeService->validateCodeFormat('EMP-1'));
        $this->assertFalse($this->codeService->validateCodeFormat('EMP-0001'));
        $this->assertFalse($this->codeService->validateCodeFormat('EMP001'));
        $this->assertFalse($this->codeService->validateCodeFormat('XXX-001'));
    }

    public function test_extracts_role_from_code(): void
    {
        $this->assertEquals(StoreMemberRole::Owner, $this->codeService->getRoleFromCode('OWN-001'));
        $this->assertEquals(StoreMemberRole::Manager, $this->codeService->getRoleFromCode('MGR-001'));
        $this->assertEquals(StoreMemberRole::Cashier, $this->codeService->getRoleFromCode('EMP-001'));
        $this->assertEquals(StoreMemberRole::Kitchen, $this->codeService->getRoleFromCode('KIT-001'));

        $this->assertNull($this->codeService->getRoleFromCode('XXX-001'));
    }
}

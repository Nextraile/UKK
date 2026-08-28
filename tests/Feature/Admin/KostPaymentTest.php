<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test payment information management for Kost.
 *
 * Covers QRIS image upload, bank account info update, validation,
 * file naming pattern, old file deletion, and authorization.
 */
class KostPaymentTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Setup test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /**
     * Test admin can upload QRIS image with correct filename pattern.
     */
    public function test_admin_can_upload_qris_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('qris.jpg');

        $response = $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            ['qris_image' => $file]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Informasi pembayaran berhasil diperbarui.');

        $kost->refresh();

        // Verify filename pattern: UUID v4 (security: prevent enumeration)
        $this->assertNotNull($kost->qris_image_path);
        $this->assertMatchesRegularExpression(
            '/^qris\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|jpeg|png)$/',
            $kost->qris_image_path
        );

        Storage::disk('public')->assertExists($kost->qris_image_path);
    }

    /**
     * Test old QRIS image is deleted when uploading new one.
     */
    public function test_old_qris_image_deleted_on_replace(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Upload first QRIS
        $oldFile = UploadedFile::fake()->image('qris-old.jpg');
        $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            ['qris_image' => $oldFile]
        );

        $kost->refresh();
        $oldPath = $kost->qris_image_path;
        Storage::disk('public')->assertExists($oldPath);

        // Upload new QRIS
        $newFile = UploadedFile::fake()->image('qris-new.png');
        $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            ['qris_image' => $newFile]
        );

        $kost->refresh();

        // Old file should be deleted
        Storage::disk('public')->assertMissing($oldPath);
        // New file should exist
        Storage::disk('public')->assertExists($kost->qris_image_path);
    }

    /**
     * Test admin can update bank account information.
     */
    public function test_admin_can_update_bank_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            [
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $kost->refresh();
        $this->assertEquals('BCA', $kost->bank_name);
        $this->assertEquals('1234567890', $kost->account_number);
        $this->assertEquals('John Doe', $kost->account_holder_name);
    }

    /**
     * Test validation: QRIS must be image file.
     */
    public function test_qris_image_must_be_valid_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            ['qris_image' => $file]
        );

        $response->assertSessionHasErrors('qris_image');
    }

    /**
     * Test validation: QRIS max size 2MB.
     */
    public function test_qris_image_max_size_2mb(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create 3MB file (exceeds limit)
        $file = UploadedFile::fake()->create('large.jpg', 3072);

        $response = $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            ['qris_image' => $file]
        );

        $response->assertSessionHasErrors('qris_image');
    }

    /**
     * Test validation: bank_name required if account_number provided.
     */
    public function test_bank_name_required_with_account_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            [
                'account_number' => '1234567890',
                // bank_name missing
            ]
        );

        $response->assertSessionHasErrors('bank_name');
    }

    /**
     * Test validation: account_holder_name required if account_number provided.
     */
    public function test_account_holder_name_required_with_account_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->patch(
            route('admin.kosts.payment.update', $kost),
            [
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                // account_holder_name missing
            ]
        );

        $response->assertSessionHasErrors('account_holder_name');
    }

    /**
     * Test authorization: only kost owner can update payment info.
     */
    public function test_only_kost_owner_can_update_payment(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherAdmin)->patch(
            route('admin.kosts.payment.update', $kost),
            ['bank_name' => 'BCA']
        );

        $response->assertForbidden();
    }

    /**
     * Test tenant cannot update payment info.
     */
    public function test_tenant_cannot_update_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($tenant)->patch(
            route('admin.kosts.payment.update', $kost),
            ['bank_name' => 'BCA']
        );

        $response->assertForbidden();
    }

    /**
     * Test guest cannot update payment info.
     */
    public function test_guest_cannot_update_payment(): void
    {
        $kost = Kost::factory()->create();

        $response = $this->patch(
            route('admin.kosts.payment.update', $kost),
            ['bank_name' => 'BCA']
        );

        $response->assertRedirect(route('login'));
    }
}

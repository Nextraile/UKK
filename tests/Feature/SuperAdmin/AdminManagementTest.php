<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Domain\Identity\Mail\AdminAccountCreated;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'superadmin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ==================== LIST TESTS ====================

    public function test_superadmin_can_view_admin_list(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.admins.index'));

        $response->assertOk();
        $response->assertViewIs('super-admin.admins.index');
        $response->assertViewHas('admins');
    }

    public function test_admin_list_paginates_at_20_per_page(): void
    {
        User::factory()->count(25)->create(['role' => 'admin']);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.admins.index'));

        $response->assertOk();
        $admins = $response->viewData('admins');
        $this->assertCount(20, $admins->items());
        $this->assertEquals(26, $admins->total()); // 25 + setUp admin
    }

    public function test_superadmin_can_filter_deleted_admins(): void
    {
        $deletedAdmin = User::factory()->create(['role' => 'admin', 'deleted_at' => now()]);

        // Without filter: deleted admin NOT shown
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.admins.index'));
        $response->assertDontSee($deletedAdmin->email);

        // With filter: deleted admin shown
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.admins.index', ['show_deleted' => 1]));
        $response->assertSee($deletedAdmin->email);
    }

    // ==================== CREATE TESTS ====================

    public function test_superadmin_can_create_admin_account(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'New',
                'last_name' => 'Admin',
                'email' => 'newadmin@example.com',
                'phone' => '081234567890',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('super-admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'phone' => '081234567890',
            'role' => 'admin',
            'first_name' => 'New',
            'last_name' => 'Admin',
        ]);

        $admin = User::where('email', 'newadmin@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertNull($admin->email_verified_at); // Must verify via OTP
    }

    public function test_creating_admin_sends_email(): void
    {
        Mail::fake();

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'Email',
                'last_name' => 'Test',
                'email' => 'emailtest@example.com',
                'phone' => '081234567891',
                'password' => 'password123',
            ]);

        Mail::assertSent(AdminAccountCreated::class, function ($mail) {
            return $mail->admin->email === 'emailtest@example.com' &&
                   $mail->password === 'password123';
        });
    }

    // ==================== VALIDATION TESTS ====================

    public function test_validation_requires_password_on_create(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'No',
                'last_name' => 'Password',
                'email' => 'nopass@example.com',
                'phone' => '081234567892',
                // password missing
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_validation_requires_unique_email(): void
    {
        User::factory()->create(['role' => 'admin', 'email' => 'existing@example.com']);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'Duplicate',
                'last_name' => 'Email',
                'email' => 'existing@example.com',
                'phone' => '081234567893',
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_validation_requires_minimum_8_characters_password(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'Short',
                'last_name' => 'Password',
                'email' => 'shortpass@example.com',
                'phone' => '081234567894',
                'password' => 'short', // Only 5 characters
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_validation_requires_phone_on_create(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                // phone missing
            ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_validation_requires_valid_phone_format(): void
    {
        // Invalid format: doesn't start with 08
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '0712345678', // Invalid: starts with 07
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors(['phone']);

        // Invalid format: too short
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john2@example.com',
                'phone' => '081234', // Invalid: too short (< 10 digits)
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors(['phone']);

        // Invalid format: too long
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john3@example.com',
                'phone' => '08123456789012345', // Invalid: too long (> 13 digits)
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_phone_must_be_unique(): void
    {
        // Create admin with specific phone
        User::factory()->create([
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        // Try to create another admin with same phone
        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.admins.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@example.com',
                'phone' => '081234567890', // Duplicate phone
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_phone_can_stay_same_on_update(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        // Update admin keeping same phone
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.admins.update', $admin), [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => $admin->email,
                'phone' => '081234567890', // Same phone
            ]);

        $response->assertRedirect(route('super-admin.admins.index'));
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'phone' => '081234567890',
        ]);
    }

    public function test_phone_must_be_unique_on_update(): void
    {
        // Create two admins with different phones
        $admin1 = User::factory()->create([
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        $admin2 = User::factory()->create([
            'role' => 'admin',
            'phone' => '089876543210',
        ]);

        // Try to update admin2 with admin1's phone
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.admins.update', $admin2), [
                'first_name' => $admin2->first_name,
                'last_name' => $admin2->last_name,
                'email' => $admin2->email,
                'phone' => '081234567890', // admin1's phone
            ]);

        $response->assertSessionHasErrors(['phone']);
    }

    // ==================== UPDATE TESTS ====================

    public function test_superadmin_can_update_admin_info(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.admins.update', $this->admin), [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => $this->admin->email,
                'phone' => $this->admin->phone,
            ]);

        $response->assertRedirect(route('super-admin.admins.index'));
        $response->assertSessionHas('success');

        $this->admin->refresh();
        $this->assertEquals('Updated', $this->admin->first_name);
        $this->assertEquals('Name', $this->admin->last_name);
    }

    public function test_superadmin_can_update_admin_email(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.admins.update', $this->admin), [
                'first_name' => $this->admin->first_name,
                'last_name' => $this->admin->last_name,
                'email' => 'newemail@example.com',
                'phone' => $this->admin->phone,
            ]);

        $this->admin->refresh();
        $this->assertEquals('newemail@example.com', $this->admin->email);
    }

    public function test_updating_admin_does_not_change_role(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.admins.update', $this->admin), [
                'first_name' => $this->admin->first_name,
                'last_name' => $this->admin->last_name,
                'email' => $this->admin->email,
                'phone' => $this->admin->phone,
                'role' => 'superadmin', // Attempt to escalate privilege
            ]);

        $this->admin->refresh();
        $this->assertEquals('admin', $this->admin->role); // Role unchanged
    }

    // ==================== DELETE TESTS ====================

    public function test_superadmin_can_soft_delete_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.admins.destroy', $this->admin));

        $response->assertRedirect(route('super-admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $this->admin->id]);
    }

    public function test_superadmin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.admins.destroy', $this->superAdmin));

        $response->assertRedirect(route('super-admin.admins.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id, 'deleted_at' => null]);
    }

    public function test_deleted_admin_cannot_login(): void
    {
        $this->admin->delete();

        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password', // Assuming factory default
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_admin_cannot_access_admin_management(): void
    {
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($regularAdmin)
            ->get(route('super-admin.admins.index'));

        $response->assertForbidden(); // 403
    }
}

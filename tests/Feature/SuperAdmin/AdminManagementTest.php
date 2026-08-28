<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Domain\Identity\Models\User;
use App\Mail\AdminAccountCreated;
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
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('super-admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
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
                'password' => 'short', // Only 5 characters
            ]);

        $response->assertSessionHasErrors('password');
    }

    // ==================== UPDATE TESTS ====================

    public function test_superadmin_can_update_admin_info(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('super-admin.admins.update', $this->admin), [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => $this->admin->email,
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

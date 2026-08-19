<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Identity;

use App\Domain\Identity\Models\OtpVerification;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A user can be created via the factory with the expected attributes.
     *
     * FR-001: User registration creates a user record with first_name,
     * last_name, email, phone, role, avatar_path.
     */
    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'role' => 'user',
        ]);
    }

    /**
     * The role helper methods correctly identify each role.
     *
     * FR-007: Role-based access — isTenant(), isAdmin(), isSuperAdmin().
     */
    public function test_user_role_helpers(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        $this->assertTrue($tenant->isTenant());
        $this->assertFalse($tenant->isAdmin());
        $this->assertFalse($tenant->isSuperAdmin());

        $this->assertFalse($admin->isTenant());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isSuperAdmin());

        $this->assertFalse($superAdmin->isTenant());
        $this->assertFalse($superAdmin->isAdmin());
        $this->assertTrue($superAdmin->isSuperAdmin());
    }

    /**
     * Soft-deleting a user sets deleted_at but keeps the record in the DB.
     *
     * FR-012: Account deletion is a soft delete.
     */
    public function test_user_soft_delete(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * A soft-deleted user can be restored, clearing deleted_at.
     *
     * FR-012: Soft delete is reversible via restore.
     */
    public function test_user_restore(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $user->restore();

        $this->assertNull($user->fresh()->deleted_at);
    }

    /**
     * The otpVerifications relation returns associated OTP records.
     *
     * FR-004: OTP verification records belong to a user.
     */
    public function test_user_otp_verifications_relation(): void
    {
        $user = User::factory()->create();
        $otp = OtpVerification::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->otpVerifications);
        $this->assertTrue($user->otpVerifications->first()->is($otp));
    }

    /**
     * The fillable attributes include the expected fields.
     *
     * The `role` field is intentionally excluded from fillable to
     * prevent mass-assignment; it must be set explicitly.
     */
    public function test_user_fillable_attributes(): void
    {
        $user = new User;

        $this->assertSame(
            ['first_name', 'last_name', 'email', 'password', 'phone', 'avatar_path'],
            $user->getFillable(),
        );
    }

    /**
     * The hidden attributes include password and remember_token.
     */
    public function test_user_hidden_attributes(): void
    {
        $user = new User;

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }

    /**
     * The casts array maps email_verified_at and deleted_at to datetime,
     * and password to hashed.
     */
    public function test_user_casts(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this->assertSame('datetime', $casts['email_verified_at']);
        $this->assertSame('hashed', $casts['password']);
        $this->assertSame('datetime', $casts['deleted_at']);
    }

    /**
     * The factory states unverified(), admin(), superAdmin(), and deleted()
     * produce users with the corresponding attributes.
     */
    public function test_user_factory_states(): void
    {
        $unverified = User::factory()->unverified()->create();
        $this->assertNull($unverified->email_verified_at);

        $admin = User::factory()->admin()->create();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());

        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertSame('superadmin', $superAdmin->role);
        $this->assertTrue($superAdmin->isSuperAdmin());

        $deleted = User::factory()->deleted()->create();
        $this->assertNotNull($deleted->deleted_at);
        $this->assertTrue($deleted->trashed());
    }
}

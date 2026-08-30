<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for tenant rental index (dashboard) functionality.
 *
 * Covers:
 * - Stats calculation (pending_actions excludes confirmed, cancelled count)
 * - View rendering (stat cards, tabs, cancellation reason display)
 * - Authorization (user can only see own rentals, guests redirected)
 */
class RentalIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create verified tenant
        $this->tenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Test that stats pending_actions only includes pending and paid statuses,
     * excluding confirmed status (FR-096, controller logic line 55).
     *
     * @test
     */
    public function test_stats_pending_actions_excludes_confirmed_status(): void
    {
        // Create rentals with different statuses
        Rental::factory()->for($this->tenant, 'user')->pending()->count(2)->create();
        Rental::factory()->for($this->tenant, 'user')->paid()->create();
        Rental::factory()->for($this->tenant, 'user')->confirmed()->create(); // Should be excluded
        Rental::factory()->for($this->tenant, 'user')->active()->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Assert stats calculation
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['pending_actions'], 'pending_actions should only count pending (2) + paid (1), excluding confirmed');
        $this->assertEquals(1, $stats['active']);
    }

    /**
     * Test that stats includes cancelled count (FR-127, new feature).
     *
     * @test
     */
    public function test_stats_includes_cancelled_count(): void
    {
        // Create cancelled rentals
        Rental::factory()->for($this->tenant, 'user')->cancelled()->count(3)->create();
        Rental::factory()->for($this->tenant, 'user')->active()->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Assert cancelled stat exists and is accurate
        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('cancelled', $stats);
        $this->assertEquals(3, $stats['cancelled']);
        $this->assertEquals(1, $stats['active']);
    }

    /**
     * Test that stats are accurate with mixed rental statuses.
     *
     * @test
     */
    public function test_stats_accurate_with_mixed_statuses(): void
    {
        // Create shared resources to avoid factory overflow
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
        ]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        // Create rentals with all possible statuses (reuse room/scheme)
        Rental::factory()->for($this->tenant, 'user')->active()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->active()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->pending()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->paid()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->paid()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->confirmed()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]); // Not in pending_actions
        Rental::factory()->for($this->tenant, 'user')->completed()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->completed()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->cancelled()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);
        Rental::factory()->for($this->tenant, 'user')->cancelled()->create(['room_id' => $room->id, 'price_scheme_id' => $priceScheme->id]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Assert all stats
        $stats = $response->viewData('stats');
        $this->assertEquals(2, $stats['active']);
        $this->assertEquals(3, $stats['pending_actions'], 'pending_actions = pending (1) + paid (2)');
        $this->assertEquals(2, $stats['completed']);
        $this->assertEquals(2, $stats['cancelled']);
    }

    /**
     * Test that stats are correct when user has no rentals.
     *
     * @test
     */
    public function test_stats_correct_when_no_rentals_exist(): void
    {
        // Don't create any rentals for this tenant
        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Assert all stats are zero
        $stats = $response->viewData('stats');
        $this->assertEquals(0, $stats['active']);
        $this->assertEquals(0, $stats['pending_actions']);
        $this->assertEquals(0, $stats['completed']);
        $this->assertEquals(0, $stats['cancelled']);
    }

    /**
     * Test that view displays cancelled stat card (Phase 1C).
     *
     * @test
     */
    public function test_view_displays_cancelled_stat_card(): void
    {
        Rental::factory()->for($this->tenant, 'user')->cancelled()->count(5)->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('Dibatalkan'); // Stat card label
        $response->assertSee('5'); // Cancelled count
        $response->assertSee('text-error-600'); // Icon color class
    }

    /**
     * Test that view displays cancelled tab filter (Phase 1A).
     *
     * @test
     */
    public function test_view_displays_cancelled_tab_filter(): void
    {
        Rental::factory()->for($this->tenant, 'user')->cancelled()->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('Dibatalkan', false); // Tab button text
        $response->assertSee("filter = 'cancelled'", false); // Alpine.js click handler
        $response->assertSee('cancelledCount', false); // Alpine.js count binding
    }

    /**
     * Test that view shows cancellation reason for cancelled rentals (FR-127, Phase 2C).
     *
     * @test
     */
    public function test_view_shows_cancellation_reason_for_cancelled_rentals(): void
    {
        $cancellationReason = 'Tidak jadi sewa karena sudah dapat kost lain';

        Rental::factory()
            ->for($this->tenant, 'user')
            ->cancelled()
            ->create([
                'cancelled_reason' => $cancellationReason,
                'cancelled_at' => now()->subHours(2),
            ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('Alasan Pembatalan:', false);
        $response->assertSee($cancellationReason, false);
        $response->assertSee('Dibatalkan pada:', false);
    }

    /**
     * Test that view does not show cancellation reason for non-cancelled rentals.
     *
     * @test
     */
    public function test_view_does_not_show_cancellation_reason_for_active_rentals(): void
    {
        Rental::factory()->for($this->tenant, 'user')->active()->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Alasan Pembatalan:', false);
    }

    /**
     * Test that view shows filter-specific empty message when no cancelled rentals (Phase 1D).
     *
     * @test
     */
    public function test_view_shows_cancelled_empty_state_when_filter_has_no_results(): void
    {
        // Create only active rental (no cancelled)
        Rental::factory()->for($this->tenant, 'user')->active()->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Check for cancelled-specific empty state template
        $response->assertSee("filter === 'cancelled' && cancelledCount === 0", false);
        $response->assertSee('Tidak ada rental dibatalkan', false);
        $response->assertSee('Rental yang dibatalkan akan muncul di sini', false);
    }

    /**
     * Test that view shows generic empty state when user has no rentals at all.
     *
     * @test
     */
    public function test_view_shows_generic_empty_state_when_no_rentals(): void
    {
        // Don't create any rentals
        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('Belum ada rental', false);
        $response->assertSee('Mulai cari kost dan buat booking pertama Anda', false);
        $response->assertSee('Cari Kost', false);
    }

    /**
     * Test that user can only see their own rentals (authorization check).
     *
     * @test
     */
    public function test_user_can_only_see_own_rentals(): void
    {
        // Create rentals for different users
        $rentalA = Rental::factory()->for($this->tenant, 'user')->active()->create();

        $otherTenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $rentalB = Rental::factory()->for($otherTenant, 'user')->active()->create();

        // Login as tenant A
        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Assert only tenant A's rental is in the view data
        $rentals = $response->viewData('rentals');
        $this->assertCount(1, $rentals);
        $this->assertTrue($rentals->contains('id', $rentalA->id));
        $this->assertFalse($rentals->contains('id', $rentalB->id));

        // Assert tenant B's kost name is not visible
        $response->assertDontSee($rentalB->room->roomType->kost->name, false);
    }

    /**
     * Test that guest users are redirected to login page.
     *
     * @test
     */
    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('rentals.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that view displays payment deadline indicator for pending rentals (Phase 2C).
     *
     * @test
     */
    public function test_view_displays_payment_deadline_for_pending_rentals(): void
    {
        $rental = Rental::factory()
            ->for($this->tenant, 'user')
            ->pending()
            ->create();

        // Set payment expiry to 36 hours from now (normal deadline, not near)
        $rental->payment->update([
            'expired_at' => now()->addHours(36),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('Deadline pembayaran:', false);
    }

    /**
     * Test that view displays warning for near-deadline payments (within 24 hours).
     *
     * @test
     */
    public function test_view_displays_warning_for_near_deadline_payments(): void
    {
        $rental = Rental::factory()
            ->for($this->tenant, 'user')
            ->pending()
            ->create();

        // Set payment expiry to 12 hours from now (near deadline)
        $rental->payment->update([
            'expired_at' => now()->addHours(12),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('bg-warning-50', false); // Warning background
        $response->assertSee('text-warning-600', false); // Warning icon color
    }

    /**
     * Test that view displays error indicator for expired payments.
     *
     * @test
     */
    public function test_view_displays_error_for_expired_payments(): void
    {
        $rental = Rental::factory()
            ->for($this->tenant, 'user')
            ->pending()
            ->create();

        // Set payment expiry to past
        $rental->payment->update([
            'expired_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertSee('Pembayaran Kedaluwarsa', false);
        $response->assertSee('bg-error-50', false); // Error background
        $response->assertSee('text-error-600', false); // Error icon color
    }

    /**
     * Test that view does not display payment deadline for confirmed rentals.
     *
     * @test
     */
    public function test_view_does_not_display_payment_deadline_for_confirmed_rentals(): void
    {
        Rental::factory()
            ->for($this->tenant, 'user')
            ->confirmed()
            ->create();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Deadline pembayaran:', false);
    }

    /**
     * Test that rentals are ordered by created_at descending (newest first).
     *
     * @test
     */
    public function test_rentals_ordered_by_created_at_descending(): void
    {
        // Create rentals with different timestamps
        $oldRental = Rental::factory()
            ->for($this->tenant, 'user')
            ->active()
            ->create(['created_at' => now()->subDays(10)]);

        $newRental = Rental::factory()
            ->for($this->tenant, 'user')
            ->pending()
            ->create(['created_at' => now()->subDays(1)]);

        $newestRental = Rental::factory()
            ->for($this->tenant, 'user')
            ->paid()
            ->create(['created_at' => now()]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $response->assertStatus(200);

        // Assert order in view data
        $rentals = $response->viewData('rentals');
        $this->assertEquals($newestRental->id, $rentals[0]->id);
        $this->assertEquals($newRental->id, $rentals[1]->id);
        $this->assertEquals($oldRental->id, $rentals[2]->id);
    }

    /**
     * Test that view includes eager loaded relationships to avoid N+1 queries.
     *
     * @test
     */
    public function test_view_eager_loads_relationships(): void
    {
        Rental::factory()->for($this->tenant, 'user')->active()->count(5)->create();

        // Enable query log
        \DB::enableQueryLog();

        $response = $this->actingAs($this->tenant)->get(route('rentals.index'));

        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        $response->assertStatus(200);

        // Assert no N+1 queries (should have initial query + 1 eager load query)
        // We allow some flexibility here since there are multiple relationships
        $this->assertLessThan(10, count($queries), 'Query count suggests N+1 problem');
    }
}

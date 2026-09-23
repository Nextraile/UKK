<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Mail\RentalCreatedMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\RoomInventory\Models\PriceScheme;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\RoomInventory\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RentalCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private Room $room;

    private PriceScheme $priceScheme;

    protected function setUp(): void
    {
        parent::setUp();

        // Create verified tenant
        $this->tenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Create active kost with room type and room
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'active',
            'qris_image_path' => 'qris/test.jpg',
        ]);

        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
            'security_deposit' => 500000,
        ]);

        $this->priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1500000,
            'is_active' => true,
        ]);

        $this->room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
            'code' => 'A01',
        ]);
    }

    public function test_authenticated_verified_tenant_can_create_rental(): void
    {
        Mail::fake();

        $startDate = now()->addDays(4)->format('Y-m-d');

        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => $startDate,
                'duration' => 3,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert rental created
        $this->assertDatabaseHas('rentals', [
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'status' => 'payment_pending',
            'duration_value' => 3,
            'duration_unit' => 'month',
            'room_price' => 1500000,
            'security_deposit' => 500000,
            'grand_total' => 5000000, // (1500000 * 3) + 500000
        ]);

        $rental = Rental::first();

        // Assert payment created with 48h expiry
        $this->assertDatabaseHas('payments', [
            'rental_id' => $rental->id,
            'amount' => 5000000,
            'status' => 'pending', // Payment status, NOT rental status
        ]);

        $payment = Payment::first();
        $this->assertEqualsWithDelta(
            now()->addHours(48)->timestamp,
            $payment->expired_at->timestamp,
            60 // 1 minute tolerance
        );

        // Assert status history created
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'payment_pending',
            'changed_by' => $this->tenant->id,
        ]);

        // Assert email queued
        Mail::assertQueued(RentalCreatedMail::class, function ($mail) use ($rental) {
            return $mail->rental->id === $rental->id;
        });

        // Assert room used_slots incremented (via accessor)
        $this->assertEquals(1, $this->room->fresh()->used_slots);
        $this->assertEquals(1, $this->room->fresh()->free_slots);
    }

    public function test_start_date_must_be_at_least_4_days_from_now(): void
    {
        $startDate = now()->addDays(3)->format('Y-m-d'); // Too soon

        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => $startDate,
                'duration' => 1,
            ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_start_date_cannot_be_more_than_30_days_from_now(): void
    {
        $startDate = now()->addDays(31)->format('Y-m-d'); // Too far

        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => $startDate,
                'duration' => 1,
            ]);

        $response->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->post(route('rentals.store'), [
            'room_id' => $this->room->id,
            'price_scheme_id' => $this->priceScheme->id,
            'start_date' => now()->addDays(4)->format('Y-m-d'),
            'duration' => 1,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_unverified_email_user_cannot_create_rental(): void
    {
        $unverifiedUser = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($unverifiedUser)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => now()->addDays(4)->format('Y-m-d'),
                'duration' => 1,
            ]);

        // Middleware redirects unverified users (302), not 403
        $response->assertRedirect();
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_cannot_book_room_with_no_free_slots(): void
    {
        // Fill room to capacity (max_occupants = 2) - create manually without factory
        $otherTenant = User::factory()->create(['role' => 'user']);

        for ($i = 0; $i < 2; $i++) {
            Rental::create([
                'room_id' => $this->room->id,
                'user_id' => $otherTenant->id,
                'price_scheme_id' => $this->priceScheme->id,
                'duration_value' => 1,
                'duration_unit' => 'month',
                'room_price' => 1500000,
                'security_deposit' => 500000,
                'grand_total' => 2000000,
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(35),
                'status' => 'active',
            ]);
        }

        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => now()->addDays(4)->format('Y-m-d'),
                'duration' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('room_id');
        $this->assertStringContainsString('penuh', session('errors')->first('room_id'));

        // Only 2 existing rentals, new one not created
        $this->assertDatabaseCount('rentals', 2);
    }

    public function test_duration_is_correctly_calculated_for_different_units(): void
    {
        // Test month duration
        $monthScheme = PriceScheme::factory()->create([
            'room_type_id' => $this->room->room_type_id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1000000,
        ]);

        $startDate = now()->addDays(5);

        $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $monthScheme->id,
                'start_date' => $startDate->format('Y-m-d'),
                'duration' => 2,
            ]);

        $rental = Rental::first();
        $expectedEndDate = $startDate->copy()->addMonths(2);

        $this->assertEquals(
            $expectedEndDate->format('Y-m-d'),
            $rental->end_date->format('Y-m-d')
        );
    }

    public function test_cannot_book_room_when_period_overlaps_existing_booking(): void
    {
        // Create a single-occupant room for this test
        $singleRoom = Room::factory()->create([
            'kost_id' => $this->room->kost_id,
            'room_type_id' => $this->room->room_type_id,
            'status' => 'available',
            'code' => 'B01',
        ]);

        // Update room type to max_occupants = 1 for this specific test
        $singleRoom->roomType->update(['max_occupants' => 1]);

        // Create existing rental with valid dates (starting in 5 days)
        $existingStart = now()->addDays(5);
        $existingEnd = $existingStart->copy()->addMonth();

        Rental::create([
            'room_id' => $singleRoom->id,
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'room_price' => 1500000,
            'security_deposit' => 500000,
            'grand_total' => 2000000,
            'start_date' => $existingStart,
            'end_date' => $existingEnd,
            'status' => 'active',
        ]);

        // Try to book: overlapping period (starting 10 days from now, overlaps with existing)
        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $singleRoom->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'duration' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('room_id');
        $this->assertStringContainsString('penuh untuk periode', session('errors')->first('room_id'));

        // Only the existing rental, new one not created
        $this->assertDatabaseCount('rentals', 1);
    }

    public function test_can_book_room_for_non_overlapping_period(): void
    {
        // Create existing rental (starting in 5 days)
        $existingStart = now()->addDays(5);
        $existingEnd = $existingStart->copy()->addDays(15); // 15 day rental

        Rental::create([
            'room_id' => $this->room->id,
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'room_price' => 1500000,
            'security_deposit' => 500000,
            'grand_total' => 2000000,
            'start_date' => $existingStart,
            'end_date' => $existingEnd,
            'status' => 'active',
        ]);

        Mail::fake();

        // Book: starting after existing rental ends (22 days from now, no overlap)
        $newStart = now()->addDays(22);

        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.store'), [
                'room_id' => $this->room->id,
                'price_scheme_id' => $this->priceScheme->id,
                'start_date' => $newStart->format('Y-m-d'),
                'duration' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Both rentals should exist
        $this->assertDatabaseCount('rentals', 2);
        $this->assertDatabaseHas('rentals', [
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'status' => 'payment_pending',
        ]);
    }
}

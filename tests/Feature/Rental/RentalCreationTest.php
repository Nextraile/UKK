<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Mail\RentalCreatedMail;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
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
            'status' => 'pending',
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
            'status' => 'pending',
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
            'status' => 'pending',
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

    public function test_concurrent_bookings_prevented_by_pessimistic_locking(): void
    {
        $this->markTestSkipped('Concurrency test requires parallel execution - run manually with Apache Bench');

        // This test documents expected behavior for manual testing:
        // 1. Run: ab -n 100 -c 10 -p rental.json -T application/json http://localhost/rentals
        // 2. Expected: Only 2 rentals created (max_occupants = 2)
        // 3. Expected: 98 requests return RoomFullException

        // For automated testing, we can verify pessimistic lock is used
        $this->assertTrue(
            str_contains(
                file_get_contents(app_path('Domain/Rental/Actions/CreateRental.php')),
                'lockForUpdate()'
            ),
            'CreateRental Action must use lockForUpdate() for pessimistic locking'
        );
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
}

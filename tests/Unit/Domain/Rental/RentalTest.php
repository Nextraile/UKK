<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RentalTest extends TestCase
{
    use RefreshDatabase;

    public function test_rental_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $rental->user);
        $this->assertEquals($user->id, $rental->user->id);
    }

    public function test_rental_belongs_to_room(): void
    {
        $room = Room::factory()->create();
        $rental = Rental::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(Room::class, $rental->room);
        $this->assertEquals($room->id, $rental->room->id);
    }

    public function test_rental_belongs_to_price_scheme(): void
    {
        $priceScheme = PriceScheme::factory()->create();
        $rental = Rental::factory()->create(['price_scheme_id' => $priceScheme->id]);

        $this->assertInstanceOf(PriceScheme::class, $rental->priceScheme);
        $this->assertEquals($priceScheme->id, $rental->priceScheme->id);
    }

    public function test_rental_has_one_payment(): void
    {
        $rental = Rental::factory()->create();

        // Rental factory already creates payment, so just verify relationship
        $this->assertInstanceOf(Payment::class, $rental->payment);
        $this->assertEquals($rental->id, $rental->payment->rental_id);
    }

    public function test_rental_has_many_documents(): void
    {
        $rental = Rental::factory()->create();
        $document1 = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
        ]);
        $document2 = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KK',
        ]);

        $this->assertCount(2, $rental->rentalDocuments);
        $this->assertTrue($rental->rentalDocuments->contains($document1));
        $this->assertTrue($rental->rentalDocuments->contains($document2));
    }

    public function test_documents_is_alias_for_rental_documents(): void
    {
        $rental = Rental::factory()->create();
        RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
        ]);
        RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KK',
        ]);
        RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'Slip Gaji',
        ]);

        $this->assertEquals($rental->rentalDocuments->count(), $rental->documents->count());
        $this->assertEquals($rental->rentalDocuments->pluck('id'), $rental->documents->pluck('id'));
    }

    public function test_rental_has_many_status_histories(): void
    {
        $rental = Rental::factory()->create();
        // Factory already creates 1 status history (pending), add 2 more
        $history1 = RentalStatusHistory::factory()->create(['rental_id' => $rental->id]);
        $history2 = RentalStatusHistory::factory()->create(['rental_id' => $rental->id]);

        $rental->refresh(); // Refresh to load the new relationships

        // Should have 3 total: 1 from factory + 2 we just created
        $this->assertCount(3, $rental->statusHistories);
        $this->assertTrue($rental->statusHistories->contains($history1));
        $this->assertTrue($rental->statusHistories->contains($history2));
    }

    public function test_date_fields_are_cast_to_datetime(): void
    {
        $rental = Rental::factory()->create([
            'start_date' => '2026-09-01 00:00:00',
            'end_date' => '2026-12-01 00:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $rental->start_date);
        $this->assertInstanceOf(Carbon::class, $rental->end_date);
        $this->assertEquals('2026-09-01', $rental->start_date->toDateString());
        $this->assertEquals('2026-12-01', $rental->end_date->toDateString());
    }

    public function test_nullable_datetime_fields_cast_correctly(): void
    {
        $rental = Rental::factory()->create([
            'cancelled_at' => null,
            'confirmed_at' => null,
            'activated_at' => null,
            'completed_at' => null,
        ]);

        $this->assertNull($rental->cancelled_at);
        $this->assertNull($rental->confirmed_at);
        $this->assertNull($rental->activated_at);
        $this->assertNull($rental->completed_at);
    }

    public function test_cancelled_at_casts_to_datetime_when_set(): void
    {
        $rental = Rental::factory()->create([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $rental->cancelled_at);
    }

    public function test_confirmed_at_casts_to_datetime_when_set(): void
    {
        $rental = Rental::factory()->create([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $rental->confirmed_at);
    }

    public function test_activated_at_casts_to_datetime_when_set(): void
    {
        $rental = Rental::factory()->create([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $rental->activated_at);
    }

    public function test_completed_at_casts_to_datetime_when_set(): void
    {
        $rental = Rental::factory()->create([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $rental->completed_at);
    }

    public function test_decimal_fields_cast_correctly(): void
    {
        $rental = Rental::factory()->create([
            'room_price' => 1500000.50,
            'security_deposit' => 500000.00,
            'grand_total' => 2000000.50,
        ]);

        $this->assertEquals('1500000.50', $rental->room_price);
        $this->assertEquals('500000.00', $rental->security_deposit);
        $this->assertEquals('2000000.50', $rental->grand_total);
    }

    public function test_status_field_stored_as_string(): void
    {
        $rental = Rental::factory()->create(['status' => 'pending']);

        $this->assertIsString($rental->status);
        $this->assertEquals('pending', $rental->status);
    }

    public function test_fillable_attributes_can_be_mass_assigned(): void
    {
        $data = [
            'room_id' => Room::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'price_scheme_id' => PriceScheme::factory()->create()->id,
            'duration_value' => 3,
            'duration_unit' => 'month',
            'room_price' => 1500000,
            'security_deposit' => 500000,
            'grand_total' => 2000000,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addMonths(3)->addDays(5),
            'status' => 'pending',
        ];

        $rental = Rental::create($data);

        $this->assertEquals($data['room_id'], $rental->room_id);
        $this->assertEquals($data['user_id'], $rental->user_id);
        $this->assertEquals($data['price_scheme_id'], $rental->price_scheme_id);
        $this->assertEquals($data['duration_value'], $rental->duration_value);
        $this->assertEquals($data['duration_unit'], $rental->duration_unit);
        $this->assertEquals($data['status'], $rental->status);
    }

    public function test_cancelled_reason_can_be_null(): void
    {
        $rental = Rental::factory()->create([
            'status' => 'pending',
            'cancelled_reason' => null,
        ]);

        $this->assertNull($rental->cancelled_reason);
    }

    public function test_cancelled_reason_can_be_set(): void
    {
        $rental = Rental::factory()->create([
            'status' => 'cancelled',
            'cancelled_reason' => 'Tenant requested cancellation',
        ]);

        $this->assertEquals('Tenant requested cancellation', $rental->cancelled_reason);
    }

    public function test_rental_can_be_created_via_factory(): void
    {
        $rental = Rental::factory()->create();

        $this->assertInstanceOf(Rental::class, $rental);
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
        ]);
    }

    public function test_rental_relationships_eager_loaded(): void
    {
        $rental = Rental::factory()->create();
        // Payment already created by factory
        RentalDocument::factory()->create(['rental_id' => $rental->id, 'document_type' => 'KTP']);
        RentalDocument::factory()->create(['rental_id' => $rental->id, 'document_type' => 'KK']);
        RentalStatusHistory::factory()->count(3)->create(['rental_id' => $rental->id]);

        $loadedRental = Rental::with(['user', 'room', 'priceScheme', 'payment', 'rentalDocuments', 'statusHistories'])
            ->find($rental->id);

        $this->assertTrue($loadedRental->relationLoaded('user'));
        $this->assertTrue($loadedRental->relationLoaded('room'));
        $this->assertTrue($loadedRental->relationLoaded('priceScheme'));
        $this->assertTrue($loadedRental->relationLoaded('payment'));
        $this->assertTrue($loadedRental->relationLoaded('rentalDocuments'));
        $this->assertTrue($loadedRental->relationLoaded('statusHistories'));
    }
}

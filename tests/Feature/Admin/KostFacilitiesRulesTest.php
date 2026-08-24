<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test facilities and rules dynamic list input with Alpine.js fallback.
 *
 * Covers:
 * - FR-024: Admin can add/edit facilities as JSON array
 * - FR-025: Admin can add/edit rules as JSON array
 * - Fallback for JS-disabled clients (facilities_text, rules_text)
 */
class KostFacilitiesRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Kost $kost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->admin = User::factory()->admin()->create();
        $this->kost = Kost::factory()->draft()->create([
            'user_id' => $this->admin->id,
            'facilities' => ['WiFi', 'AC'],
            'rules' => ['No smoking', 'No pets'],
        ]);
    }

    /**
     * Test admin can update facilities using array input (Alpine.js enabled).
     */
    public function test_admin_can_update_facilities_with_array_input(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities' => ['WiFi Gratis', 'AC', 'Parkir Motor', 'Laundry'],
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));
        $response->assertSessionHas('success');

        $this->kost->refresh();
        $this->assertEquals(['WiFi Gratis', 'AC', 'Parkir Motor', 'Laundry'], $this->kost->facilities);
    }

    /**
     * Test admin can update rules using array input (Alpine.js enabled).
     */
    public function test_admin_can_update_rules_with_array_input(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'rules' => ['Dilarang merokok', 'Dilarang membawa hewan', 'Jam malam 22:00'],
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));
        $response->assertSessionHas('success');

        $this->kost->refresh();
        $this->assertEquals(['Dilarang merokok', 'Dilarang membawa hewan', 'Jam malam 22:00'], $this->kost->rules);
    }

    /**
     * Test admin can clear facilities by sending empty array.
     */
    public function test_admin_can_clear_facilities(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities' => [],
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->kost->refresh();
        $this->assertEquals([], $this->kost->facilities);
    }

    /**
     * Test admin can clear rules by sending empty array.
     */
    public function test_admin_can_clear_rules(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'rules' => [],
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->kost->refresh();
        $this->assertEquals([], $this->kost->rules);
    }

    /**
     * Test facilities_text fallback for JS-disabled clients.
     *
     * When JS is disabled, textarea sends facilities_text with newline-separated values.
     * Controller parses it into array.
     */
    public function test_facilities_text_fallback_parses_line_by_line(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities_text' => "WiFi Gratis\nAC\nParkir Motor\nLaundry",
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->kost->refresh();
        $this->assertEquals(['WiFi Gratis', 'AC', 'Parkir Motor', 'Laundry'], $this->kost->facilities);
    }

    /**
     * Test rules_text fallback for JS-disabled clients.
     */
    public function test_rules_text_fallback_parses_line_by_line(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'rules_text' => "Dilarang merokok\nDilarang membawa hewan\nJam malam 22:00",
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->kost->refresh();
        $this->assertEquals(['Dilarang merokok', 'Dilarang membawa hewan', 'Jam malam 22:00'], $this->kost->rules);
    }

    /**
     * Test fallback ignores empty lines and trims whitespace.
     */
    public function test_fallback_ignores_empty_lines_and_trims_whitespace(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities_text' => "  WiFi Gratis  \n\nAC\n  \nParkir Motor",
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->kost->refresh();
        $this->assertEquals(['WiFi Gratis', 'AC', 'Parkir Motor'], $this->kost->facilities);
    }

    /**
     * Test array input takes precedence over fallback text.
     *
     * If both facilities[] and facilities_text are sent, use facilities[].
     */
    public function test_array_input_takes_precedence_over_text_fallback(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities' => ['WiFi Gratis', 'AC'],
                    'facilities_text' => "Should be ignored\nBecause array exists",
                ]
            );

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->kost->refresh();
        $this->assertEquals(['WiFi Gratis', 'AC'], $this->kost->facilities);
    }

    /**
     * Test validation rejects facility item longer than 255 chars.
     */
    public function test_validation_rejects_facility_longer_than_255_chars(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities' => [str_repeat('A', 256)],
                ]
            );

        $response->assertSessionHasErrors('facilities.0');
    }

    /**
     * Test validation rejects rule item longer than 255 chars.
     */
    public function test_validation_rejects_rule_longer_than_255_chars(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'rules' => [str_repeat('B', 256)],
                ]
            );

        $response->assertSessionHasErrors('rules.0');
    }

    /**
     * Test non-owner admin cannot update another admin's kost.
     */
    public function test_non_owner_admin_cannot_update_facilities(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($otherAdmin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(
                route('admin.kosts.update', $this->kost),
                [
                    'name' => $this->kost->name,
                    'contact_number' => $this->kost->contact_number,
                    'facilities' => ['Hacked'],
                ]
            );

        $response->assertForbidden();
    }
}

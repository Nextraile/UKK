<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Landing page tests (PAGE-001).
 *
 * @see PAGES.md §2 PAGE-001 (lines 66-172)
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test landing page loads successfully.
     */
    public function test_landing_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('Temukan Kost Impian Anda');
        $response->assertSee('Kost Terpopuler');
        $response->assertSee('Cara Kerja');
        $response->assertSee('Testimoni Penyewa');
    }

    /**
     * Test landing page displays featured kosts.
     */
    public function test_landing_page_displays_featured_kosts(): void
    {
        // Create active kosts
        $kost1 = Kost::factory()->active()->create(['name' => 'Kost A']);
        $kost2 = Kost::factory()->active()->create(['name' => 'Kost B']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Kost A');
        $response->assertSee('Kost B');
    }

    /**
     * Test landing page shows empty state when no kosts.
     */
    public function test_landing_page_shows_empty_state_when_no_kosts(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Belum ada kost tersedia');
    }

    /**
     * Test landing page only shows active kosts.
     */
    public function test_landing_page_only_shows_active_kosts(): void
    {
        $activeKost = Kost::factory()->active()->create(['name' => 'Active Kost']);
        $draftKost = Kost::factory()->draft()->create(['name' => 'Draft Kost']);
        $pendingKost = Kost::factory()->pendingReview()->create(['name' => 'Pending Kost']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Active Kost');
        $response->assertDontSee('Draft Kost');
        $response->assertDontSee('Pending Kost');
    }

    /**
     * Test landing page shows testimonials.
     */
    public function test_landing_page_shows_testimonials(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Rina Marlina');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Siti Nurhaliza');
    }

    /**
     * Test landing page has CTA buttons.
     */
    public function test_landing_page_has_cta_buttons(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Cari Kost');
        $response->assertSee('Daftar Sekarang');
        $response->assertSee(route('marketplace.index'));
        $response->assertSee(route('register'));
    }
}

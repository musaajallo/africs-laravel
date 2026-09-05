<?php

namespace Tests\Feature\Console;

use App\Models\ExchangeRate;
use App\Models\User;
use App\Support\ExchangeRates;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function manager(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        return $user;
    }

    public function test_to_base_returns_one_for_the_base_currency(): void
    {
        $this->assertSame('1', ExchangeRates::toBase('GMD'));
    }

    public function test_to_base_returns_the_latest_rate_on_or_before_the_date(): void
    {
        ExchangeRate::factory()->for_currency('USD')->on('2026-01-01')->create(['rate' => '60.0000000000']);
        ExchangeRate::factory()->for_currency('USD')->on('2026-06-01')->create(['rate' => '72.0000000000']);

        $this->assertSame('72.0000000000', ExchangeRates::toBase('USD', now()->parse('2026-08-01')));
        $this->assertSame('60.0000000000', ExchangeRates::toBase('USD', now()->parse('2026-03-01')));
        $this->assertNull(ExchangeRates::toBase('USD', now()->parse('2025-12-01')));
    }

    public function test_a_user_without_the_permission_cannot_see_the_screen(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/exchange-rates')->assertForbidden();
    }

    public function test_manager_can_record_a_manual_rate(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/exchange-rates', [
                'currency' => 'usd',
                'rate' => '72.5',
                'rate_date' => '2026-09-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exchange_rates', [
            'base_currency' => 'GMD',
            'quote_currency' => 'USD',
            'source' => 'manual',
        ]);
    }

    public function test_the_base_currency_is_rejected_as_a_manual_rate(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/exchange-rates', ['currency' => 'GMD', 'rate' => '1', 'rate_date' => '2026-09-01'])
            ->assertSessionHasErrors('currency');
    }

    public function test_the_fetch_command_stores_the_inverse_rate(): void
    {
        Http::fake([
            'api.exchangerate.host/*' => Http::response([
                'base' => 'GMD',
                'date' => '2026-09-05',
                'rates' => ['USD' => 0.0138, 'EUR' => 0.0128],
            ]),
        ]);

        $this->artisan('erp:fetch-exchange-rates')->assertSuccessful();

        // 1 / 0.0138 ≈ 72.4637681159
        $rate = ExchangeRate::where('quote_currency', 'USD')->first();
        $this->assertNotNull($rate);
        $this->assertSame('2026-09-05', $rate->rate_date->toDateString());
        $this->assertEqualsWithDelta(72.4637681159, (float) $rate->rate, 0.0001);
        $this->assertSame('exchangerate.host', $rate->source);
    }

    public function test_the_api_exposes_the_latest_rates(): void
    {
        ExchangeRate::factory()->for_currency('USD')->on('2026-01-01')->create(['rate' => '60']);
        ExchangeRate::factory()->for_currency('USD')->on('2026-06-01')->create(['rate' => '72']);

        $user = $this->manager();
        $token = $user->createToken('t', ['exchange-rates.view'])->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/exchange-rates');

        $response->assertOk()->assertJsonFragment([
            'quote_currency' => 'USD',
            'rate' => '72.0000000000',
            'rate_date' => '2026-06-01',
        ]);
    }
}

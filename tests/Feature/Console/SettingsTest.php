<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Support\Rbac;
use App\Support\Settings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Settings::flushCache();
    }

    protected function manager(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        return $user;
    }

    protected function validPayload(array $overrides = []): array
    {
        $base = [
            'company' => [
                'name' => 'Africs Inc',
                'legal_name' => 'Africs Incorporated',
                'email' => 'hello@africsinc.com',
                'phone' => '+220 000 0000',
                'tax_number' => 'TIN-100200',
                'address' => "1 Kairaba Avenue\nSerrekunda",
                'city' => 'Serrekunda',
                'country' => 'gm',
            ],
            'currency' => [
                'enabled' => ['GMD', 'USD'],
                'base' => 'GMD',
            ],
            'billing' => [
                'tax_label' => 'VAT',
                'tax_rate' => 15,
                'payment_terms_days' => 21,
                'payment_methods' => ['Bank transfer', 'Cash'],
            ],
        ];

        foreach ($overrides as $group => $values) {
            $base[$group] = array_replace($base[$group], $values);
        }

        return $base;
    }

    public function test_a_console_user_without_the_permission_cannot_open_settings(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/settings')->assertForbidden();
    }

    public function test_manager_sees_settings_with_defaults_merged_in(): void
    {
        $this->actingAs($this->manager())
            ->get('/console/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Settings')
                ->where('settings.currency.base', 'GMD')
                ->where('settings.billing.payment_terms_days', 30));
    }

    public function test_manager_can_save_settings(): void
    {
        $this->actingAs($this->manager())
            ->put('/console/settings', $this->validPayload())
            ->assertRedirect('/console/settings');

        Settings::flushCache();
        $this->assertSame('Africs Inc', Settings::get('company.name'));
        $this->assertSame('GM', Settings::get('company.country'));
        $this->assertEqualsCanonicalizing(['GMD', 'USD'], Settings::enabledCurrencies());
        $this->assertSame(21, Settings::paymentTermsDays());
        $this->assertSame(15.0, Settings::taxRate());
    }

    public function test_base_currency_must_be_an_enabled_currency(): void
    {
        $this->actingAs($this->manager())
            ->put('/console/settings', $this->validPayload([
                'currency' => ['enabled' => ['GMD'], 'base' => 'USD'],
            ]))
            ->assertSessionHasErrors('currency.base');
    }

    public function test_at_least_one_currency_must_be_enabled(): void
    {
        $this->actingAs($this->manager())
            ->put('/console/settings', $this->validPayload([
                'currency' => ['enabled' => [], 'base' => 'GMD'],
            ]))
            ->assertSessionHasErrors('currency.enabled');
    }

    public function test_the_client_form_offers_only_the_enabled_currencies(): void
    {
        Settings::put(['currency' => ['enabled' => ['USD', 'EUR'], 'base' => 'USD']]);

        $this->actingAs($this->manager())
            ->get('/console/clients/create')
            ->assertInertia(fn ($page) => $page->where('currencies', ['USD', 'EUR']));
    }
}

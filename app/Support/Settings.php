<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Typed, cached access to application settings. Stored one row per group in
 * the `settings` table; reads merge over the defaults below.
 */
final class Settings
{
    private const CACHE_KEY = 'app.settings';

    /** Currencies the business can invoice in. */
    public const SUPPORTED_CURRENCIES = ['GMD', 'USD', 'EUR'];

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'company' => [
                'name' => 'Africs',
                'legal_name' => '',
                'email' => '',
                'phone' => '',
                'tax_number' => '',
                'address' => '',
                'city' => '',
                'country' => 'GM',
            ],
            'currency' => [
                'enabled' => ['GMD', 'USD', 'EUR'],
                'base' => 'GMD',
            ],
            'billing' => [
                'tax_label' => 'VAT',
                'tax_rate' => 0,
                'payment_terms_days' => 30,
                'payment_methods' => ['Bank transfer', 'Cash', 'Cheque', 'Mobile money', 'Card'],
            ],
        ];
    }

    /**
     * The full, merged settings tree.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $stored = Cache::rememberForever(
            self::CACHE_KEY,
            fn () => Setting::all()->pluck('value', 'key')->toArray(),
        );

        // Merge stored groups over the defaults one level deep. A shallow
        // replace (not recursive) so list values like currency.enabled are
        // taken wholesale from storage, not index-merged with the default.
        $merged = self::defaults();

        foreach ($merged as $group => $values) {
            if (isset($stored[$group]) && is_array($stored[$group])) {
                $merged[$group] = array_replace($values, $stored[$group]);
            }
        }

        return $merged;
    }

    /**
     * Dot-notation getter, e.g. Settings::get('company.name').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return data_get(self::all(), $key, $default);
    }

    /**
     * Persist one or more groups, e.g. Settings::put(['company' => [...]]).
     *
     * @param  array<string, array<string, mixed>>  $groups
     */
    public static function put(array $groups): void
    {
        foreach ($groups as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return list<string> */
    public static function enabledCurrencies(): array
    {
        return self::get('currency.enabled', self::SUPPORTED_CURRENCIES);
    }

    public static function baseCurrency(): string
    {
        return self::get('currency.base', 'GMD');
    }

    public static function paymentTermsDays(): int
    {
        return (int) self::get('billing.payment_terms_days', 30);
    }

    /** @return list<string> */
    public static function paymentMethods(): array
    {
        $methods = self::get('billing.payment_methods', []);

        return array_values(array_filter(array_map('strval', is_array($methods) ? $methods : [])));
    }

    /** Tax rate as a percentage, e.g. 15 for 15%. */
    public static function taxRate(): float
    {
        return (float) self::get('billing.tax_rate', 0);
    }
}

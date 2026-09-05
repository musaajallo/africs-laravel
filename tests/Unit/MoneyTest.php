<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_multiply_rounds_to_the_currency_scale(): void
    {
        $this->assertSame('31.11', Money::multiply('10.37', '3', 'USD'));
        $this->assertSame('3.33', Money::multiply('1.111', '3', 'GMD'));
    }

    public function test_percentage_computes_tax(): void
    {
        $this->assertSame('15.00', Money::percentage('100.00', '15', 'USD'));
        $this->assertSame('1.88', Money::percentage('12.50', '15', 'USD'));
    }

    public function test_sum_adds_a_list_of_amounts(): void
    {
        $this->assertSame('60.00', Money::sum(['10.00', '20.00', '30.00'], 'EUR'));
        $this->assertSame('0.00', Money::sum([], 'EUR'));
    }

    public function test_to_base_applies_the_fx_rate(): void
    {
        $this->assertSame('7250.00', Money::toBase('100.00', '72.5', 'GMD'));
        $this->assertSame('100.00', Money::toBase('100.00', '1', 'GMD'));
    }

    public function test_format_is_code_prefixed(): void
    {
        $this->assertSame('USD 1,250.00', Money::format('1250', 'USD'));
    }
}

<?php

namespace Tests\Feature;

use App\Support\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_hands_out_gap_free_numbers_per_year(): void
    {
        $this->assertSame('PRO-2026-0001', Sequence::next('proforma', 'PRO', 2026));
        $this->assertSame('PRO-2026-0002', Sequence::next('proforma', 'PRO', 2026));
        $this->assertSame('PRO-2026-0003', Sequence::next('proforma', 'PRO', 2026));
    }

    public function test_each_key_has_its_own_counter(): void
    {
        Sequence::next('proforma', 'PRO', 2026);

        $this->assertSame('INV-2026-0001', Sequence::next('invoice', 'INV', 2026));
    }

    public function test_the_counter_restarts_each_year(): void
    {
        Sequence::next('invoice', 'INV', 2026);
        Sequence::next('invoice', 'INV', 2026);

        $this->assertSame('INV-2027-0001', Sequence::next('invoice', 'INV', 2027));
    }

    public function test_peek_does_not_consume_a_number(): void
    {
        $this->assertSame('PRO-2026-0001', Sequence::peek('proforma', 'PRO', 2026));
        $this->assertSame('PRO-2026-0001', Sequence::next('proforma', 'PRO', 2026));
        $this->assertSame('PRO-2026-0002', Sequence::peek('proforma', 'PRO', 2026));
    }
}

<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateFetcher;
use Illuminate\Console\Command;

class FetchExchangeRates extends Command
{
    protected $signature = 'erp:fetch-exchange-rates';

    protected $description = 'Fetch the latest FX rates for enabled currencies from exchangerate.host';

    public function handle(ExchangeRateFetcher $fetcher): int
    {
        $result = $fetcher->fetch();

        if ($result['fetched'] === []) {
            $this->warn('No rates fetched. Check EXCHANGERATE_ACCESS_KEY and connectivity — rates can still be entered manually on the FX screen.');

            return self::FAILURE;
        }

        $this->info("Fetched rates for {$result['date']}: ".implode(', ', $result['fetched']));

        if ($result['skipped'] !== []) {
            $this->warn('No rate returned for: '.implode(', ', $result['skipped']));
        }

        return self::SUCCESS;
    }
}

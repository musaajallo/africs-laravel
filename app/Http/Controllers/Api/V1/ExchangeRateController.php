<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ExchangeRateResource;
use App\Support\ExchangeRates;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExchangeRateController extends Controller
{
    /** The latest recorded rate for each enabled currency, against the base. */
    public function index(): AnonymousResourceCollection
    {
        return ExchangeRateResource::collection(ExchangeRates::latestPerCurrency());
    }
}

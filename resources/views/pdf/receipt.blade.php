@php
    $money = fn ($amount) => number_format((float) $amount, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 12px; color: #1f2937; margin: 0; }
    h1 { font-size: 22px; margin: 0 0 2px; letter-spacing: 1px; }
    .muted { color: #6b7280; }
    .row { width: 100%; }
    .row:after { content: ""; display: table; clear: both; }
    .col-left { float: left; width: 55%; }
    .col-right { float: right; width: 40%; text-align: right; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 24px; }
    table.lines th { text-align: left; border-bottom: 2px solid #111827; padding: 6px 8px; font-size: 11px; text-transform: uppercase; }
    table.lines td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
    .num { text-align: right; }
    .big { margin-top: 24px; font-size: 15px; font-weight: bold; }
    .meta td { padding: 2px 0; }
    .meta td.k { color: #6b7280; padding-right: 12px; }
    .notes { margin-top: 60px; clear: both; }
</style>
</head>
<body>
    @php $logo = public_path('images/logo.png'); @endphp
    <div class="row">
        <div class="col-left">
            @if(file_exists($logo))
                <img src="{{ $logo }}" alt="{{ $company['name'] ?? 'Africs' }}" style="height:42px">
            @else
                <h1>{{ $company['name'] ?? 'Africs' }}</h1>
            @endif
            @if(!empty($company['address']))<div class="muted" style="margin-top:4px">{{ $company['address'] }}</div>@endif
            <div class="muted">
                {{ $company['city'] ?? '' }}{{ !empty($company['country']) ? ', '.$company['country'] : '' }}
            </div>
            @if(!empty($company['email']))<div class="muted">{{ $company['email'] }}</div>@endif
        </div>
        <div class="col-right">
            <h1>Receipt</h1>
            <div class="muted">{{ $payment->number }}</div>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col-left">
            <div class="muted">Received from</div>
            <strong>{{ $payment->client->name }}</strong>
        </div>
        <div class="col-right">
            <table class="meta" style="float:right">
                <tr><td class="k">Date</td><td>{{ $payment->paid_on?->toFormattedDateString() }}</td></tr>
                <tr><td class="k">Method</td><td>{{ $payment->method }}</td></tr>
                @if($payment->reference)<tr><td class="k">Reference</td><td>{{ $payment->reference }}</td></tr>@endif
                <tr><td class="k">Currency</td><td>{{ $payment->currency }}</td></tr>
            </table>
        </div>
    </div>

    <div class="big">Amount received: {{ $payment->currency }} {{ $money($payment->amount) }}</div>

    @if($payment->allocations->isNotEmpty())
        <table class="lines">
            <thead>
                <tr><th>Applied to invoice</th><th class="num">Amount</th></tr>
            </thead>
            <tbody>
                @foreach($payment->allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->invoice?->number ?? '—' }}</td>
                        <td class="num">{{ $payment->currency }} {{ $money($allocation->amount) }}</td>
                    </tr>
                @endforeach
                @if((float) $payment->allocated_amount < (float) $payment->amount)
                    <tr>
                        <td class="muted">Unapplied (credit on account)</td>
                        <td class="num muted">{{ $payment->currency }} {{ $money($payment->amount - $payment->allocated_amount) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <div class="notes">
        @if($payment->notes)<p>{{ $payment->notes }}</p>@endif
        <p class="muted">Thank you.</p>
    </div>
</body>
</html>

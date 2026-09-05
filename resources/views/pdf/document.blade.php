@php
    /** @var \App\Models\Proforma|\App\Models\Invoice $doc */
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
    table.totals { width: 45%; float: right; border-collapse: collapse; margin-top: 12px; }
    table.totals td { padding: 4px 8px; }
    table.totals tr.grand td { border-top: 2px solid #111827; font-weight: bold; font-size: 13px; }
    .meta td { padding: 2px 0; }
    .meta td.k { color: #6b7280; padding-right: 12px; }
    .notes { margin-top: 90px; clear: both; }
    .status { display: inline-block; padding: 2px 8px; border: 1px solid #6b7280; border-radius: 3px; font-size: 10px; text-transform: uppercase; }
</style>
</head>
<body>
    <div class="row">
        <div class="col-left">
            <h1>{{ $company['name'] ?? 'Africs' }}</h1>
            @if(!empty($company['legal_name']))<div class="muted">{{ $company['legal_name'] }}</div>@endif
            @if(!empty($company['address']))<div class="muted">{{ $company['address'] }}</div>@endif
            <div class="muted">
                {{ $company['city'] ?? '' }}{{ !empty($company['country']) ? ', '.$company['country'] : '' }}
            </div>
            @if(!empty($company['email']))<div class="muted">{{ $company['email'] }}</div>@endif
            @if(!empty($company['tax_number']))<div class="muted">Tax no. {{ $company['tax_number'] }}</div>@endif
        </div>
        <div class="col-right">
            <h1>{{ $kind }}</h1>
            <div class="muted">{{ $doc->number }}</div>
            <div style="margin-top:6px"><span class="status">{{ $doc->status }}</span></div>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col-left">
            <div class="muted">Billed to</div>
            <strong>{{ $doc->client->name }}</strong><br>
            @if($doc->client->billing_address){{ $doc->client->billing_address }}<br>@endif
            {{ $doc->client->city }}{{ $doc->client->country ? ', '.$doc->client->country : '' }}<br>
            @if($doc->client->tax_number)<span class="muted">Tax no. {{ $doc->client->tax_number }}</span>@endif
        </div>
        <div class="col-right">
            <table class="meta" style="float:right">
                <tr><td class="k">Issued</td><td>{{ $doc->issue_date?->toFormattedDateString() }}</td></tr>
                @if($doc->valid_until)
                    <tr><td class="k">{{ $kind === 'Invoice' ? 'Due' : 'Valid until' }}</td><td>{{ $doc->valid_until->toFormattedDateString() }}</td></tr>
                @endif
                <tr><td class="k">Currency</td><td>{{ $doc->currency }}</td></tr>
                @if($doc->project)<tr><td class="k">Project</td><td>{{ $doc->project->name }}</td></tr>@endif
            </table>
        </div>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:50%">Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($doc->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $line->quantity, 3), '0'), '.') }}</td>
                    <td class="num">{{ $money($line->unit_price) }}</td>
                    <td class="num">{{ $money($line->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">{{ $doc->currency }} {{ $money($doc->subtotal) }}</td></tr>
        <tr><td>{{ $doc->tax_label }} ({{ rtrim(rtrim(number_format((float) $doc->tax_rate, 3), '0'), '.') }}%)</td><td class="num">{{ $doc->currency }} {{ $money($doc->tax_total) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="num">{{ $doc->currency }} {{ $money($doc->total) }}</td></tr>
    </table>

    <div class="notes">
        @if($doc->notes)<p><strong>Notes</strong><br>{{ $doc->notes }}</p>@endif
        @if($doc->terms)<p class="muted">{{ $doc->terms }}</p>@endif
    </div>
</body>
</html>

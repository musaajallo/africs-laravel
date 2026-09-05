@php
    /** @var \App\Models\Invoice $invoice */
    /** @var array<string,mixed> $company */
    $money = fn ($amount) => number_format((float) $amount, 2);

    $paid = (float) $invoice->amount_paid;
    $total = (float) $invoice->total;
    $balance = $total - $paid;
    $inFull = $balance <= 0.005;

    $allocations = $invoice->allocations->filter(fn ($a) => $a->payment !== null);

    $logo = public_path('images/logo.png');
    $sellerName = $company['legal_name'] ?: ($company['name'] ?? 'Africs');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 38px 42px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10.5px; line-height: 1.5; color: #1f2933; margin: 0; }
    .muted { color: #6b7684; }
    table { border-collapse: collapse; }
    td { vertical-align: top; }

    .brand-logo { height: 42px; }
    .doc-title { font-size: 19px; letter-spacing: 2px; text-transform: uppercase; color: #0d4d2c; font-weight: bold; }

    .stamp {
        display: inline-block; margin-top: 8px; padding: 5px 14px;
        border: 2px solid #0d4d2c; color: #0d4d2c; border-radius: 3px;
        font-size: 12px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;
    }
    .stamp.part { border-color: #a56a1a; color: #a56a1a; }

    .lead { margin-top: 26px; font-size: 11.5px; }
    .lead strong { font-weight: bold; }

    table.lines { width: 100%; margin-top: 24px; }
    table.lines thead th {
        text-align: left; font-size: 8px; letter-spacing: 1px; text-transform: uppercase;
        color: #445; border-bottom: 1.4px solid #0d4d2c; padding: 7px 7px;
    }
    table.lines tbody td { padding: 7px 7px; border-bottom: 1px solid #e4e7ec; }
    .num { text-align: right; white-space: nowrap; }

    .summary { width: 100%; margin-top: 4px; }
    .summary td.pad { width: 54%; }
    .totals td { padding: 3px 7px; }
    .totals td.k { color: #445; }
    .totals tr.grand td { border-top: 1.4px solid #0d4d2c; font-weight: bold; font-size: 11px; }

    .section { margin-top: 30px; font-size: 9.5px; }
    .foot { margin-top: 34px; padding-top: 6px; border-top: 1px solid #e4e7ec; font-size: 8.5px; color: #97a0ac; }
</style>
</head>
<body>
    <table style="width:100%">
        <tr>
            <td style="width:55%">
                @if(file_exists($logo))
                    <img class="brand-logo" src="{{ $logo }}" alt="{{ $company['name'] ?? 'Africs' }}">
                @else
                    <span class="doc-title">{{ $company['name'] ?? 'Africs' }}</span>
                @endif
            </td>
            <td style="width:45%; text-align:right">
                <div class="doc-title">Receipt</div>
                <div class="muted">for {{ $invoice->number }}</div>
                <div class="stamp {{ $inFull ? '' : 'part' }}">{{ $inFull ? 'Paid in full' : 'Part payment' }}</div>
            </td>
        </tr>
    </table>

    <div class="lead">
        Received with thanks from <strong>{{ $invoice->client->name }}</strong>
        the sum of <strong>{{ $invoice->currency }} {{ $money($paid) }}</strong>
        in {{ $inFull ? 'settlement' : 'part settlement' }} of invoice
        <strong>{{ $invoice->number }}</strong> dated {{ $invoice->issue_date?->toFormattedDateString() }}.
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Date received</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="num">Amount ({{ $invoice->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allocations as $allocation)
                <tr>
                    <td>{{ $allocation->payment->number }}</td>
                    <td>{{ $allocation->payment->paid_on?->toFormattedDateString() }}</td>
                    <td>{{ $allocation->payment->method }}</td>
                    <td>{{ $allocation->payment->reference ?: '—' }}</td>
                    <td class="num">{{ $money($allocation->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="pad"></td>
            <td>
                <table class="totals" style="width:100%">
                    <tr><td class="k">Invoice total</td><td class="num">{{ $invoice->currency }} {{ $money($total) }}</td></tr>
                    <tr><td class="k">Total received</td><td class="num">{{ $invoice->currency }} {{ $money($paid) }}</td></tr>
                    <tr class="grand">
                        <td class="k">{{ $inFull ? 'Balance' : 'Balance outstanding' }}</td>
                        <td class="num">{{ $invoice->currency }} {{ $money(max($balance, 0)) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section muted">
        Issued {{ now()->toFormattedDateString() }} by {{ $sellerName }}. This receipt confirms funds received
        against the invoice above and does not alter the terms of that invoice.
    </div>

    <div class="foot">
        {{ $sellerName }}@if(!empty($company['tax_number'])) &nbsp;&middot;&nbsp; Tax {{ $company['tax_number'] }}@endif
        @if(!empty($company['email'])) &nbsp;&middot;&nbsp; {{ $company['email'] }}@endif
    </div>
</body>
</html>

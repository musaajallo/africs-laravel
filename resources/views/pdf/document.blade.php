@php
    /** @var \App\Models\Proforma|\App\Models\Invoice $doc */
    /** @var array<string,mixed> $company */
    $isInvoice = $kind === 'Invoice';
    $taxed = (float) $doc->tax_rate > 0;
    $title = $isInvoice ? ($taxed ? 'Tax Invoice' : 'Invoice') : 'Proforma Invoice';

    $money = fn ($amount) => number_format((float) $amount, 2);
    $qty = fn ($n) => rtrim(rtrim(number_format((float) $n, 3), '0'), '.');
    $rate = rtrim(rtrim(number_format((float) $doc->tax_rate, 3), '0'), '.');

    $amountPaid = $isInvoice ? (float) $doc->amount_paid : 0.0;
    $balanceDue = (float) $doc->total - $amountPaid;

    $logo = public_path('images/logo.png');
    $dueLabel = $isInvoice ? 'Payment due' : 'Valid until';
    $dueDate = $isInvoice ? $doc->due_date : $doc->valid_until;
    $sellerName = $company['legal_name'] ?: ($company['name'] ?? 'Africs');

    $cityCountry = fn ($city, $country) => trim(implode(', ', array_filter([$city, $country])));
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

    .header td { padding: 0; }
    .brand-logo { height: 42px; }
    .doc-title { font-size: 19px; letter-spacing: 2px; text-transform: uppercase; color: #0d4d2c; font-weight: bold; }
    .doc-number { font-weight: bold; font-size: 11px; }

    .grid { width: 100%; margin-top: 28px; }
    .grid td { padding-right: 16px; }
    .grid td:last-child { padding-right: 0; }
    .label { font-size: 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #8b93a0; margin-bottom: 3px; }
    .pname { font-size: 11px; font-weight: bold; }
    .meta-row td { padding: 1px 0; }
    .meta-row td.k { color: #6b7684; padding-right: 12px; white-space: nowrap; }
    .meta-row td.v { text-align: right; }

    table.lines { width: 100%; margin-top: 30px; }
    table.lines thead th {
        text-align: left; font-size: 8px; letter-spacing: 1px; text-transform: uppercase;
        color: #445; border-bottom: 1.4px solid #0d4d2c; padding: 7px 7px;
    }
    table.lines tbody td { padding: 7px 7px; border-bottom: 1px solid #e4e7ec; }
    table.lines .n { width: 20px; color: #aab1bc; }
    .num { text-align: right; white-space: nowrap; }

    .summary { width: 100%; margin-top: 4px; }
    .summary td.pad { width: 54%; }
    .totals td { padding: 3px 7px; }
    .totals td.k { color: #445; }
    .totals tr.grand td { border-top: 1.4px solid #0d4d2c; font-weight: bold; font-size: 11px; }
    .totals tr.due td { border-top: 2px solid #0d4d2c; font-weight: bold; font-size: 12px; color: #0d4d2c; }

    .fx-note { text-align: right; font-size: 9.5px; color: #6b7684; margin-top: 6px; }

    .section { margin-top: 26px; }
    .section h3 { font-size: 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #8b93a0; margin: 0 0 3px; }
    .section .body { white-space: pre-line; }

    .proforma-note {
        margin-top: 24px; padding: 8px 10px; font-size: 9.5px;
        background: #f4f6f4; border-left: 3px solid #0d4d2c; color: #33413a;
    }

    .foot { margin-top: 34px; padding-top: 6px; border-top: 1px solid #e4e7ec; font-size: 8.5px; color: #97a0ac; }
</style>
</head>
<body>
    <table class="header" style="width:100%">
        <tr>
            <td style="width:55%">
                @if(file_exists($logo))
                    <img class="brand-logo" src="{{ $logo }}" alt="{{ $company['name'] ?? 'Africs' }}">
                @else
                    <span class="doc-title">{{ $company['name'] ?? 'Africs' }}</span>
                @endif
            </td>
            <td style="width:45%; text-align:right">
                <div class="doc-title">{{ $title }}</div>
                <div class="doc-number">{{ $doc->number }}</div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td style="width:33%">
                <div class="label">From</div>
                <div class="pname">{{ $sellerName }}</div>
                @if(!empty($company['address'])){!! nl2br(e($company['address'])) !!}<br>@endif
                @if($cityCountry($company['city'] ?? '', $company['country'] ?? '')){{ $cityCountry($company['city'] ?? '', $company['country'] ?? '') }}<br>@endif
                @if(!empty($company['registration_number']))<span class="muted">Reg. No. {{ $company['registration_number'] }}</span><br>@endif
                @if(!empty($company['tax_number']))<span class="muted">{{ $doc->tax_label ?: 'Tax' }} No. {{ $company['tax_number'] }}</span><br>@endif
                @if(!empty($company['email']))<span class="muted">{{ $company['email'] }}</span><br>@endif
                @if(!empty($company['phone']))<span class="muted">{{ $company['phone'] }}</span>@endif
            </td>
            <td style="width:33%">
                <div class="label">Bill to</div>
                <div class="pname">{{ $doc->client->name }}</div>
                @if($doc->client->billing_address){!! nl2br(e($doc->client->billing_address)) !!}<br>@endif
                @if($cityCountry($doc->client->city, $doc->client->country)){{ $cityCountry($doc->client->city, $doc->client->country) }}<br>@endif
                @if($doc->client->tax_number)<span class="muted">Tax No. {{ $doc->client->tax_number }}</span><br>@endif
                @if($doc->client->email)<span class="muted">{{ $doc->client->email }}</span>@endif
            </td>
            <td style="width:34%">
                <table class="meta-row" style="width:100%">
                    <tr><td class="k">Issued</td><td class="v">{{ $doc->issue_date?->toFormattedDateString() }}</td></tr>
                    @if($dueDate)
                        <tr><td class="k">{{ $dueLabel }}</td><td class="v">{{ $dueDate->toFormattedDateString() }}</td></tr>
                    @endif
                    <tr><td class="k">Currency</td><td class="v">{{ $doc->currency }}</td></tr>
                    @if($doc->project)<tr><td class="k">Project</td><td class="v">{{ \Illuminate\Support\Str::limit($doc->project->name, 26) }}</td></tr>@endif
                </table>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th class="n">#</th>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Amount ({{ $doc->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($doc->lines as $i => $line)
                <tr>
                    <td class="n">{{ $i + 1 }}</td>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ $qty($line->quantity) }}</td>
                    <td class="num">{{ $money($line->unit_price) }}</td>
                    <td class="num">{{ $money($line->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="pad"></td>
            <td>
                <table class="totals" style="width:100%">
                    <tr><td class="k">Subtotal (net)</td><td class="num">{{ $doc->currency }} {{ $money($doc->subtotal) }}</td></tr>
                    <tr><td class="k">{{ $doc->tax_label ?: 'Tax' }} @ {{ $rate }}%</td><td class="num">{{ $doc->currency }} {{ $money($doc->tax_total) }}</td></tr>
                    <tr class="grand"><td class="k">Total</td><td class="num">{{ $doc->currency }} {{ $money($doc->total) }}</td></tr>
                    @if($isInvoice && $amountPaid > 0)
                        <tr><td class="k">Less paid</td><td class="num">&minus; {{ $doc->currency }} {{ $money($amountPaid) }}</td></tr>
                    @endif
                    @if($isInvoice)
                        <tr class="due"><td class="k">Amount due</td><td class="num">{{ $doc->currency }} {{ $money(max($balanceDue, 0)) }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($doc->currency !== $base)
        <div class="fx-note">
            Equivalent in {{ $base }}: {{ $base }} {{ $money($doc->base_total) }}
            (1 {{ $doc->currency }} = {{ rtrim(rtrim(number_format((float) $doc->fx_rate, 6), '0'), '.') }} {{ $base }})
        </div>
    @endif

    @if($isInvoice && !empty($company['bank_details']))
        <div class="section">
            <h3>Payment details</h3>
            <div class="body">{{ $company['bank_details'] }}</div>
        </div>
    @endif

    @if($doc->notes)
        <div class="section"><h3>Notes</h3><div class="body">{{ $doc->notes }}</div></div>
    @endif

    @if($doc->terms)
        <div class="section"><h3>Terms &amp; conditions</h3><div class="body">{{ $doc->terms }}</div></div>
    @endif

    @unless($isInvoice)
        <div class="proforma-note">
            This is a proforma invoice issued for quotation purposes. It is not a tax invoice and not a
            demand for payment. A tax invoice will be issued once the order is confirmed.
        </div>
    @endunless

    <div class="foot">
        {{ $sellerName }}@if(!empty($company['registration_number'])) &nbsp;&middot;&nbsp; Reg. {{ $company['registration_number'] }}@endif
        @if(!empty($company['tax_number'])) &nbsp;&middot;&nbsp; {{ $doc->tax_label ?: 'Tax' }} {{ $company['tax_number'] }}@endif
        @if(!empty($company['email'])) &nbsp;&middot;&nbsp; {{ $company['email'] }}@endif
    </div>
</body>
</html>

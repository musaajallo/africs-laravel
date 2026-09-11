<?php
/** @var \Illuminate\Support\Carbon $generatedAt */
$logo = public_path('images/logo.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 40px 46px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10px; line-height: 1.55; color: #1f2933; margin: 0; }
    .muted { color: #6b7684; }

    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }

    .header td { padding: 0; }
    .brand-logo { height: 38px; }
    .doc-title { font-size: 18px; letter-spacing: 1.2px; color: #0d4d2c; font-weight: bold; margin: 0; }
    .doc-subtitle { font-size: 10.5px; color: #6b7684; margin-top: 2px; }
    .doc-meta { text-align: right; font-size: 8.5px; color: #97a0ac; }

    h2.section-title {
        margin: 26px 0 4px; font-size: 8.5px; letter-spacing: 1.6px; text-transform: uppercase;
        color: #8b93a0; border-bottom: 1.4px solid #0d4d2c; padding-bottom: 4px;
    }

    p.lede { margin: 6px 0 0; font-size: 10px; line-height: 1.6; }

    table.clusters, table.crosswalk, table.ministries, table.extend {
        margin-top: 10px; font-size: 9px;
    }
    table.clusters th, table.crosswalk th, table.ministries th, table.extend th {
        text-align: left; font-size: 8px; letter-spacing: 0.6px; text-transform: uppercase;
        color: #445; border-bottom: 1.2px solid #0d4d2c; padding: 5px 6px;
    }
    table.clusters td, table.crosswalk td, table.ministries td, table.extend td {
        padding: 5px 6px; border-bottom: 1px solid #e4e7ec;
    }
    table.ministries td.cluster-tag, table.crosswalk td.code, table.extend td.code { white-space: nowrap; }
    .cluster-pill {
        display: inline-block; font-size: 7.5px; letter-spacing: 0.4px; text-transform: uppercase;
        color: #0d4d2c; background: #eaf1ec; border-radius: 3px; padding: 1.5px 5px; margin: 0 3px 2px 0;
    }

    .note {
        margin-top: 14px; padding: 8px 10px; font-size: 9px; line-height: 1.55;
        background: #f4f6f4; border-left: 3px solid #0d4d2c; color: #33413a;
    }
    .note ul { margin: 4px 0 0 14px; padding: 0; }
    .note li { margin-bottom: 3px; }

    .sources { margin-top: 10px; font-size: 8.5px; color: #6b7684; }

    .foot { margin-top: 26px; padding-top: 6px; border-top: 1px solid #e4e7ec; font-size: 8px; color: #97a0ac; }
</style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:60%">
                @if(file_exists($logo))
                    <img class="brand-logo" src="{{ $logo }}" alt="Africs">
                @endif
                <p class="doc-title">How we classify this work</p>
                <p class="doc-subtitle">Government ministry crosswalk &mdash; Limitless Africs</p>
            </td>
            <td style="width:40%" class="doc-meta">
                Generated {{ $generatedAt->toFormattedDateString() }}<br>
                limitless@africsinc.com
            </td>
        </tr>
    </table>

    <p class="lede">
        Limitless Africs doesn&rsquo;t confine its work to a fixed set of sectors &mdash; we go where the
        gap is. On our website, projects are tagged with plain-language clusters rather than a formal
        government taxonomy, so a general visitor can understand the scope of the work without reading a
        functional classification manual. This document is for the audience that wants that rigor &mdash;
        funders, government partners, and anyone auditing how we tag our work &mdash; showing how those
        clusters relate to the UN Classification of the Functions of Government (COFOG), and how the
        Government of The Gambia&rsquo;s own ministries map onto them.
    </p>

    <h2 class="section-title">The seven plain-language clusters</h2>
    <table class="clusters">
        <thead>
            <tr><th style="width:26%">Cluster</th><th>What it covers, in plain language</th></tr>
        </thead>
        <tbody>
            <tr><td><strong>Governance</strong></td><td>Civic participation, local/regional administration, youth governance structures</td></tr>
            <tr><td><strong>Economy &amp; Livelihoods</strong></td><td>Trade, finance, labour, entrepreneurship</td></tr>
            <tr><td><strong>Foreign &amp; Security</strong></td><td>External relations, defence, justice, public order</td></tr>
            <tr><td><strong>Human Capital</strong></td><td>Health, education, women&rsquo;s affairs</td></tr>
            <tr><td><strong>Production &amp; Natural Resources</strong></td><td>Agriculture, fisheries, water, environment, energy</td></tr>
            <tr><td><strong>Infrastructure &amp; Territory</strong></td><td>Transport, works, communication, land</td></tr>
            <tr><td><strong>Social &amp; Culture</strong></td><td>Tourism, culture, sport</td></tr>
        </tbody>
    </table>

    <h2 class="section-title">Cluster &rarr; COFOG crosswalk (as published on the website)</h2>
    <table class="crosswalk">
        <thead>
            <tr><th style="width:32%">Plain-language cluster</th><th>COFOG codes covered</th></tr>
        </thead>
        <tbody>
            <tr><td>Governance</td><td class="code">01.1 (executive organs, local administration)</td></tr>
            <tr><td>Economy &amp; Livelihoods</td><td class="code">01.1 (fiscal affairs), 04.1, 04.2, 04.6</td></tr>
            <tr><td>Foreign &amp; Security</td><td class="code">01.1 (external affairs), 02, 03.1, 03.3</td></tr>
            <tr><td>Human Capital</td><td class="code">07, 09.1&ndash;09.4, 09.8, 10</td></tr>
            <tr><td>Production &amp; Natural Resources</td><td class="code">04.2, 04.3, 05, 06.3</td></tr>
            <tr><td>Infrastructure &amp; Territory</td><td class="code">04.4, 04.5, 06.2</td></tr>
            <tr><td>Social &amp; Culture</td><td class="code">04.7, 08.1, 08.2</td></tr>
        </tbody>
    </table>
    <p class="muted" style="margin-top:6px; font-size: 8.5px;">
        Several codes appear under more than one cluster (01.1 appears three times; 04.2 appears twice)
        because government functions genuinely overlap. This is expected, not an error to fix.
    </p>

    <h2 class="section-title">Extending the crosswalk for full ministry coverage</h2>
    <p class="lede" style="margin-top: 4px;">
        Three ministries carry a function slightly outside the codes above. Rather than force a fit, the
        crosswalk is extended with the adjacent COFOG subcategory it actually falls under &mdash; keeping the
        same divisions used on the website.
    </p>
    <table class="extend">
        <thead>
            <tr><th style="width:22%">Additional code</th><th style="width:24%">Added to cluster</th><th>Why</th></tr>
        </thead>
        <tbody>
            <tr>
                <td class="code">01.3 (general services)</td>
                <td>Governance</td>
                <td>Hosts the Ministry of Public Service &mdash; administration of the civil service as a whole, not one executive organ</td>
            </tr>
            <tr>
                <td class="code">06.1 (housing development)</td>
                <td>Infrastructure &amp; Territory</td>
                <td>Hosts the land/housing function inside Lands and Regional Government</td>
            </tr>
            <tr>
                <td class="code">08.3 (broadcasting &amp; publishing)</td>
                <td>Social &amp; Culture</td>
                <td>Hosts the Ministry of Information, Media and Broadcasting Services</td>
            </tr>
        </tbody>
    </table>

    <h2 class="section-title">Government of The Gambia &mdash; ministry-to-cluster mapping</h2>
    <table class="ministries">
        <thead>
            <tr>
                <th style="width:40%">Ministry / portfolio</th>
                <th style="width:20%">COFOG code(s)</th>
                <th>Cluster(s)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Office of the President / Vice President</td><td class="code">01.1</td><td class="cluster-tag"><span class="cluster-pill">Governance</span></td></tr>
            <tr><td>Attorney General and Minister of Justice</td><td class="code">03.3</td><td class="cluster-tag"><span class="cluster-pill">Foreign &amp; Security</span></td></tr>
            <tr><td>Minister of Finance and Economic Affairs</td><td class="code">01.1 (fiscal affairs)</td><td class="cluster-tag"><span class="cluster-pill">Economy &amp; Livelihoods</span></td></tr>
            <tr><td>Minister of Foreign Affairs, International Cooperation and Gambians Abroad</td><td class="code">01.1 (external affairs)</td><td class="cluster-tag"><span class="cluster-pill">Foreign &amp; Security</span></td></tr>
            <tr><td>Minister of Interior</td><td class="code">03.1</td><td class="cluster-tag"><span class="cluster-pill">Foreign &amp; Security</span></td></tr>
            <tr><td>Minister of Defence</td><td class="code">02</td><td class="cluster-tag"><span class="cluster-pill">Foreign &amp; Security</span></td></tr>
            <tr><td>Minister of Tourism and Culture</td><td class="code">04.7, 08.2</td><td class="cluster-tag"><span class="cluster-pill">Social &amp; Culture</span></td></tr>
            <tr><td>Minister of Transport, Works and Infrastructure</td><td class="code">04.4, 04.5</td><td class="cluster-tag"><span class="cluster-pill">Infrastructure &amp; Territory</span></td></tr>
            <tr><td>Minister of Fisheries and Water Resources</td><td class="code">04.2, 06.3</td><td class="cluster-tag"><span class="cluster-pill">Production &amp; Natural Resources</span></td></tr>
            <tr><td>Minister of Health</td><td class="code">07</td><td class="cluster-tag"><span class="cluster-pill">Human Capital</span></td></tr>
            <tr><td>Minister of Basic and Secondary Education</td><td class="code">09.1, 09.2</td><td class="cluster-tag"><span class="cluster-pill">Human Capital</span></td></tr>
            <tr><td>Minister of Higher Education, Research, Science and Technology</td><td class="code">09.4</td><td class="cluster-tag"><span class="cluster-pill">Human Capital</span></td></tr>
            <tr><td>Minister of Lands and Regional Government</td><td class="code">01.1 (local admin.), 06.1, 06.2</td><td class="cluster-tag"><span class="cluster-pill">Governance</span><span class="cluster-pill">Infrastructure &amp; Territory</span></td></tr>
            <tr><td>Minister of Agriculture</td><td class="code">04.2</td><td class="cluster-tag"><span class="cluster-pill">Production &amp; Natural Resources</span><span class="cluster-pill">Economy &amp; Livelihoods</span></td></tr>
            <tr><td>Minister of Trade, Industry, Regional Integration and Employment</td><td class="code">04.1</td><td class="cluster-tag"><span class="cluster-pill">Economy &amp; Livelihoods</span></td></tr>
            <tr><td>Minister of Petroleum and Energy</td><td class="code">04.3</td><td class="cluster-tag"><span class="cluster-pill">Production &amp; Natural Resources</span></td></tr>
            <tr><td>Minister of Public Service</td><td class="code">01.3</td><td class="cluster-tag"><span class="cluster-pill">Governance</span></td></tr>
            <tr><td>Minister of Gender, Children and Social Welfare</td><td class="code">10</td><td class="cluster-tag"><span class="cluster-pill">Human Capital</span></td></tr>
            <tr><td>Minister of Environment, Climate Change and Natural Resources</td><td class="code">05</td><td class="cluster-tag"><span class="cluster-pill">Production &amp; Natural Resources</span></td></tr>
            <tr><td>Minister of Information, Media and Broadcasting Services</td><td class="code">08.3</td><td class="cluster-tag"><span class="cluster-pill">Social &amp; Culture</span></td></tr>
            <tr><td>Minister of Communications and Digital Economy</td><td class="code">04.6</td><td class="cluster-tag"><span class="cluster-pill">Economy &amp; Livelihoods</span></td></tr>
            <tr><td>Minister of Youth and Sports</td><td class="code">08.1</td><td class="cluster-tag"><span class="cluster-pill">Social &amp; Culture</span><span class="cluster-pill">Governance</span></td></tr>
        </tbody>
    </table>

    <div class="note">
        <strong>Reading this table</strong>
        <ul>
            <li>A ministry with two cluster pills genuinely does both &mdash; e.g. Lands and Regional
                Government sits inside Governance (it runs local administration) and Infrastructure &amp;
                Territory (it runs land and housing policy). We don&rsquo;t force a single box.</li>
            <li>Cabinet portfolios are reshuffled from time to time &mdash; ministries are occasionally
                split, merged, or renamed. This table reflects the structure published by the Office of the
                President as of {{ $generatedAt->format('F Y') }}; recheck the source pages below before
                relying on it if this document is more than a few months old.</li>
            <li>This mapping is Limitless Africs&rsquo; own classification work, done for transparency. It
                is not an official Government of The Gambia document and carries no endorsement from any
                ministry.</li>
        </ul>
    </div>

    <p class="sources">
        Sources: Office of the President, State House of The Gambia &mdash; Cabinet
        (op.gov.gm/cabinet); Government of The Gambia &mdash; Cabinet (gambia.gov.gm/cabinet).
        Accessed {{ $generatedAt->format('F Y') }}.
    </p>

    <div class="foot">
        Africs &nbsp;&middot;&nbsp; Limitless Africs &nbsp;&middot;&nbsp; Banjul, The Gambia &nbsp;&middot;&nbsp; limitless@africsinc.com
    </div>
</body>
</html>

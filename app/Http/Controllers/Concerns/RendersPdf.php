<?php

namespace App\Http\Controllers\Concerns;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait RendersPdf
{
    /**
     * Render a Blade view to PDF. Streams inline so it opens in the browser's
     * viewer (or an in-app <iframe>); pass ?download=1 to force a download.
     *
     * @param  array<string, mixed>  $data
     */
    protected function pdfResponse(Request $request, string $view, array $data, string $filename): Response
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4');

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}

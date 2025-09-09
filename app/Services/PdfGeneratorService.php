<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\QuoteLine;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGeneratorService
{
    protected $quote;

    public function generatePdf(int $quoteId): string
    {
        $this->quote = Quote::with(['customer', 'quoteDetails.quoteLines'])->findOrFail($quoteId);
        
        $html = $this->generateHtml();

        $pdf = new Dompdf($this->getPdfOptions());
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }

    protected function generateHtml(): string
    {
        $view = view('quotes.pdf', [
            'quote' => $this->quote,
        ]);

        return $view->render();
    }

    protected function getPdfOptions(): Options
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        return $options;
    }
}
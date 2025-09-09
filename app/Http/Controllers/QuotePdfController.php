<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\QuoteLine;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;

class QuotePdfController extends Controller
{
    protected $pdfGenerator;

    public function __construct(PdfGeneratorService $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generate($id)
    {
        // El servicio retorna el contenido binario del PDF
        $pdfContent = $this->pdfGenerator->generatePdf($id);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cotizacion.pdf"',
        ]);
    }
}
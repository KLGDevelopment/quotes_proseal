<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\QuoteLine;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;

class QuoteController extends Controller
{

    public function cloneQuote($quoteId){
        $quote = Quote::find($quoteId);
        $newQuote = $quote->replicate();
        $newQuote->status = 0;
        $newQuote->save();

        foreach($quote->details as $detail){
            $newDetail = $detail->replicate();
            $newDetail->quote_id = $newQuote->id;
            $newDetail->save();

            foreach($detail->lines as $line){
                $newLine = $line->replicate();
                $newLine->quote_detail_id = $newDetail->id;
                $newLine->save();

                foreach($line->quoteDetailMaster as $detailMaster){
                    $newDetailMaster = $detailMaster->replicate();
                    $newDetailMaster->quote_line_id = $newLine->id;
                    $newDetailMaster->save();
                }
            }
        }

        return redirect()->to('/quotes/'.$newQuote->id.'/details');
    }
}
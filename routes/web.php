<?php

use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuotePdfController;
use App\Livewire\QuoteDetails;
use App\Livewire\QuoteDetailMasters;
use App\Livewire\QuoteLines;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

// Incluir rutas de autenticación
require __DIR__.'/auth.php';

// Incluir rutas de administrador
require __DIR__.'/admin.php';

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/quotes/{parentId}/details', QuoteDetails::class)->name('quotes.details');
Route::get('/quotes/{quoteId}/details/{quoteDetailId}/lines', QuoteLines::class)->name('quotes.lines');
Route::get('/quotes/{quoteId}/details/{quoteDetailId}/lines/{quoteLineId}/master', QuoteDetailMasters::class)->name('quotes.detail.masters');

Route::middleware(['web', 'auth'])
    ->prefix('odoo_masters')
    ->group(function () {
        Route::get('/products', \App\Livewire\Products::class);
        Route::get('/customers', \App\Livewire\Customers::class);
        Route::get('/branch_offices', \App\Livewire\BranchOffices::class);
        Route::get('/divisions', \App\Livewire\Divisions::class);
        Route::get('/equipments', \App\Livewire\Equipments::class);
    });


Route::middleware(['web', 'auth'])
    
    ->group(function () {
        Route::get('/quotes', \App\Livewire\Quotes::class)->name('quotes');

        //Route::get('/quotes/{quoteId}/details/{detailQuoteId}/master', QuoteDetailMasters::class)->name('quote_detail_master');
    });


    Route::get('/api/products', function (Request $request) {
    $search = $request->get('q', '');

    return Product::whereNot('code', 'LIKE', 'COT-%')
        ->where(function($q) use ($search) {
            $q->where('code', 'like', "%$search%")
              ->orWhere('name', 'like', "%$search%");
        })
        ->orderBy('name')
        ->limit(20)
        ->get()
        ->map(fn($p) => [
            'id' => $p->id,
            'text' => "{$p->code} - {$p->name}"
        ]);
});

Route::get('/quotes/{id}/pdf', [QuotePdfController::class, 'generate'])->name('quotes.pdf');
Route::get('/quotes/{id}/clone', [QuoteController::class, 'cloneQuote'])->name('quotes.clone');
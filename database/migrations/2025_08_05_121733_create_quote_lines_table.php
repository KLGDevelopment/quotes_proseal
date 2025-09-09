<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('quote_detail_id');
            $table->integer('product_id');
            $table->integer('quantity');
            //$table->double('unit_price',11,2)->default(0);
            $table->double('discount',11,2)->default(0);
            $table->double('sale_value',11,2)->default(0);
            
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
    }
};

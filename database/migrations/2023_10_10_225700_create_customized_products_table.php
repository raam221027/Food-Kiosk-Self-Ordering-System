<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customized_products')) {
            Schema::create('customized_products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('product_code');
                $table->text('description')->nullable();
                $table->boolean('is_disabled')->default(false);
                $table->string('photo')->nullable();
                $table->timestamps();
                $table->unsignedBigInteger('customized_product_id')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customized_products');
    }
};

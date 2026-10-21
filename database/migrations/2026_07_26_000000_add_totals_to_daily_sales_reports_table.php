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
        Schema::table('daily_sales_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_sales_reports', 'total_cash')) {
                $table->decimal('total_cash', 10, 2)->default(0.00);
            }

            if (!Schema::hasColumn('daily_sales_reports', 'total_gcash')) {
                $table->decimal('total_gcash', 10, 2)->default(0.00);
            }

            if (!Schema::hasColumn('daily_sales_reports', 'total_sales')) {
                $table->decimal('total_sales', 10, 2)->default(0.00);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_sales_reports', function (Blueprint $table) {
            $table->dropColumn(['total_cash', 'total_gcash', 'total_sales']);
        });
    }
};

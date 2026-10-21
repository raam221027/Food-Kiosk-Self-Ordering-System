<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The reports are written with updateOrCreate() keyed on report_date, which is only
     * reliable if the database enforces one row per date.
     */
    public function up(): void
    {
        // Collapse any duplicate dates that were created before the constraint existed
        $duplicateIds = DB::table('daily_sales_reports')
            ->select(DB::raw('MAX(id) as keep_id'), 'report_date')
            ->whereNotNull('report_date')
            ->groupBy('report_date')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('keep_id', 'report_date');

        foreach ($duplicateIds as $reportDate => $keepId) {
            DB::table('daily_sales_reports')
                ->where('report_date', $reportDate)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('daily_sales_reports', function (Blueprint $table) {
            $table->unique('report_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_sales_reports', function (Blueprint $table) {
            $table->dropUnique(['report_date']);
        });
    }
};

<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class DailySalesReport extends Model
{
    // Define the table associated with this model
    protected $table = 'daily_sales_reports';

    // Define the fillable columns (columns that can be mass-assigned)
    protected $fillable = [
        'report_date',
        'total_cash',
        'total_gcash',
        'total_sales',
    ];

    // Cast report_date so views can format it as a date instead of a raw string
    protected $casts = [
        'report_date' => 'date',
    ];

    // Define any other model-specific methods, relationships, or properties here
}

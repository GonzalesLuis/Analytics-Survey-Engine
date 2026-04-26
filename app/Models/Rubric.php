<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores one rubric range for a metric.
 *
 * Each row maps a score range to interpretation text and optional actions.
 */
class Rubric extends Model
{
    public $primaryKey = 'rubric_id';
    public $timestamps = false;
    protected $fillable = [
        'metric_id', 'min_score', 'max_score',
        'status_level', 'interpretation',
        'algo_interpretation', 'recommended_action'
    ];
}
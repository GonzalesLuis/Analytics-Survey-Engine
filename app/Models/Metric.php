<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lookup table for metric names.
 *
 * Metric scores are stored per session in `metric_results`.
 */
class Metric extends Model
{
    public $primaryKey = 'metric_id';
    public $timestamps = false;
    protected $fillable = ['name'];

    /**
     * Rubric rows tied to this metric.
     */
    public function rubrics() {
        return $this->hasMany(Rubric::class, 'metric_id', 'metric_id');
    }
}

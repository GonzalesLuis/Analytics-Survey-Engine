<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores a computed metric score for a session.
 *
 * Rubric text for this score is stored separately in `RubricResult`.
 */
class MetricResult extends Model
{
    public $primaryKey = 'metric_result_id';
    public $timestamps = false;
    protected $fillable = ['tutoring_session_id', 'user_id', 'metric_id', 'metric_score'];
}
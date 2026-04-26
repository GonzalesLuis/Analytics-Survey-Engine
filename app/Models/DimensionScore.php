<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores computed dimension scores for a session.
 *
 * These are derived values used by the reports/results page.
 */
class DimensionScore extends Model
{
    public $primaryKey = 'dimension_score_id';
    public $timestamps = false;
    protected $fillable = ['tutoring_session_id', 'user_id', 'dimension_id', 'avg_score', 'normalized_score'];
}
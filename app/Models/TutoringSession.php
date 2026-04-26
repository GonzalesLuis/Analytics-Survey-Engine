<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores one tutoring session.
 *
 * Responses, dimension scores, and metrics all link back to this record.
 */
class TutoringSession extends Model
{
    public $primaryKey = 'tutoring_session_id';
    public $timestamps = false;

    protected $fillable = [
        'assignment_id',
        'session_start',
        'session_end',
        'evaluated_at',
        'status',
    ];
}

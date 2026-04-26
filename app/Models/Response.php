<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores one submitted survey response.
 *
 * It links the user, session, and survey; per-question scores live in `ResponseAnswer`.
 */
class Response extends Model
{
    public $primaryKey = 'response_id';
    public $timestamps = false;
    protected $fillable = ['tutoring_session_id', 'user_id', 'survey_id', 'created_at'];

    /**
     * Answers that belong to this response.
     */
    public function answers() {
        return $this->hasMany(ResponseAnswer::class, 'response_id', 'response_id');
    }
}
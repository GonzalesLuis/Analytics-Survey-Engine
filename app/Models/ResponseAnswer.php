<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores one answer score for one question.
 */
class ResponseAnswer extends Model
{
    public $primaryKey = 'response_answer_id';
    public $timestamps = false;
    protected $fillable = ['response_id', 'question_id', 'score'];
}
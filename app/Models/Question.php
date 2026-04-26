<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores a single survey question.
 *
 * If `is_reverse` is true, the score is flipped during computation.
 */
class Question extends Model
{
    protected $primaryKey = 'question_id';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'dimension_id',
        'question_text',
        'is_reverse'
    ];
}
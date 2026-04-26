<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores a survey record (like pre-session or post-session).
 *
 * A survey has many dimensions, and each dimension has many questions.
 */
class Survey extends Model
{
    public $primaryKey = 'survey_id';
    public $timestamps = false;
    protected $fillable = ['name'];

    /**
     * Dimensions that belong to this survey.
     */
    public function dimensions() {
        return $this->hasMany(Dimension::class, 'survey_id', 'survey_id');
    }
}
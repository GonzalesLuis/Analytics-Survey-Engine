<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Groups related questions inside a survey.
 *
 * `name` is the key used in score calculations, while `category` helps with UI grouping.
 */
class Dimension extends Model
{
    public $primaryKey = 'dimension_id';
    public $timestamps = false;
    protected $fillable = ['survey_id', 'category', 'name'];

    /**
     * Questions under this dimension.
     */
    public function questions() {
        return $this->hasMany(Question::class, 'dimension_id', 'dimension_id');
    }
}
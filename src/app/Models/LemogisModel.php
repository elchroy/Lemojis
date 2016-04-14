<?php

namespace Elchroy\Lemogis\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class LemogisModel extends Eloquent
{
    /**
     * Define the table to be used for the emogis.
     */
    public $table = 'lemogis';

    /**
     * Define all the fillable properties of an emogi.
     */
    protected $fillable = ['name', 'chars', 'keywords', 'category', 'date_created', 'date_modified', 'created_by'];

    /**
     * Deactivate the timstamps for the emogi.
     * Timestamps have already been set up as properties of the emogi.
     */
    public $timestamps = [];
}

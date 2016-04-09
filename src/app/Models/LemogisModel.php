<?php

namespace Elchroy\Lemogis\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Elchroy\Lemogis\Models\LemogisUser as User;

class LemogisModel extends Eloquent
{
    public $table = "lemogis";

    protected $fillable = ['name', 'chars', 'keywords', 'category', 'date_created', 'date_modified', 'created_by' ];

    public $timestamps = [];


    // public function user()
    // {
    //     return $this->belongsTo('User');
    // }

}



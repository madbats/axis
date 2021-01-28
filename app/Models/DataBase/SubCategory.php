<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    //
    protected $table = '_categories';
    public $timestamps = false;

    protected $fillable = [
        'name_conf',
        'acronym_conf',
        'code_type',
        'type'
    ];
}

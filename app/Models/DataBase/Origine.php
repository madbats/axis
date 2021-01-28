<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Origine
 *
 * @property int $id
 *
 * @package App\Models\DataBase
 */
class Origine extends Model
{
    protected $table = '_origines';
    public $timestamps = false;

    protected $fillable = [
        'origine_type'
    ];
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function articles()
    {
        return $this->hasMany('App\Models\DataBase\Article');
    }
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function originated()
    {
        return $this->morphTo();
    }
}

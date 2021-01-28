<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Researcher
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $gender
 * @property int $orcid
 *
 * @package App\Models\DataBase
 */
class Researcher extends Model
{
    protected $table = '_researchers';
    public $timestamps = false;

    protected $casts = [
        'orcid' => 'int'
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'orcid'
    ];
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function articles()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Article',
            '_authors'
        )->using('App\Models\DataBase\Author')->withPivot('position');
    }
}

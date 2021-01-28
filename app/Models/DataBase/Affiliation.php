<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Affiliation
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models\DataBase
 */
class Affiliation extends Model
{
    protected $table = '_affiliations';
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    /**
     * Undocumented function
     *
     * @return void
     */
    public function authors()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Author',
            '_authors_affiliations'
        )->using('App\Models\DataBase\AuthorAffiliation');
    }
}

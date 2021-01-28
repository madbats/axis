<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Editor
 *
 * @property int $id
 * @property string $name
 * @property string $link
 *
 * @package App\Models\DataBase
 */
class Editor extends Model
{
    protected $table = '_editors';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'link'
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
            '_articles_editors'
        )->using('App\Models\DataBase\ArticleEditor');
    }
}

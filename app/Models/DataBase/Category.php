<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models\DataBase
 */
class Category extends Model
{
    protected $table = '_categories';
    public $timestamps = false;

    protected $fillable = [
        'name'
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
            '_articles_categories',
            'category_id',
            'article_id'
        )->using('App\Models\DataBase\ArticleCategory');
    }
}

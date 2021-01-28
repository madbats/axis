<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ArticlesCategory
 *
 * @property int $article_id
 * @property int $category_id
 *
 * @package App\Models\DataBase
 */
class ArticleCategory extends Pivot
{
    protected $table = '_articles_categories';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'article_id' => 'int',
        'category_id' => 'int'
    ];

    protected $fillable = [
        'article_id',
        'category_id'
    ];
}

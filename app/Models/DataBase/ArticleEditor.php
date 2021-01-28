<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ArticlesEditor
 *
 * @property int $article_id
 * @property int $editor_id
 *
 * @package App\Models\DataBase
 */
class ArticleEditor extends Pivot
{
    protected $table = '_articles_editors';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'article_id' => 'int',
        'editor_id' => 'int'
    ];

    protected $fillable = [
        'article_id',
        'editor_id'
    ];
}

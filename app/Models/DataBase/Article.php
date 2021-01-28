<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Article
 *
 * @property int $id
 * @property string $name
 * @property int $published
 * @property string $pdf
 * @property string $doi
 * @property string $abstract
 * @property string $full_text
 * @property int $origine_id
 *
 * @package App\Models\DataBase
 */
class Article extends Model
{
    protected $table = '_articles';
    public $timestamps = false;

    protected $casts = [
        'published' => 'int',
        'origine_id' => 'int',
        'score' => 'int'
    ];

    protected $fillable = [
        'title',
        'published',
        'pdf',
        'doi',
        'abstract',
        'score',
        'origine_id'
    ];

    /**
     * Undocumented function
     *
     * @return void
     */
    public function editor()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Editor',
            '_article_editors'
        )->using('App\Models\DataBase\ArticleEditor');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function categories()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Category',
            '_articles_categories'
        )->using('App\Models\DataBase\ArticleCategory');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function authors()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Researcher',
            '_authors'
        )->using('App\Models\DataBase\Author')->withPivot('position');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function references()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Article',
            '_references',
            'reference_id',
            'citation_id'
        )->using('App\Models\DataBase\Reference')->withPivot('citation');
    }
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function citations()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Article',
            '_references',
            'citation_id',
            'reference_id'
        )->using('App\Models\DataBase\Reference')->withPivot('citation');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function origine()
    {
        $type = Origine::find($this->origine_id)->origine_type;
        if (strcmp($type, 'journal')==0) {
            return Journal::find($this->origine_id);
        } else {
            return Colloque::find($this->origine_id);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function origineType()
    {
        return Origine::find($this->origine_id)->origine_type;
    }
}

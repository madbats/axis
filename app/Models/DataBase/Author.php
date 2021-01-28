<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use App\Models\DataBase\Affiliation;
use Illuminate\Database\QueryException;

/**
 * Class Author
 *
 * @property int $id
 * @property int $article_id
 * @property int $researcher_id
 * @property int $position
 *
 * @package App\Models\DataBase
 */
class Author extends Pivot
{
    protected $table = '_authors';
    public $timestamps = false;

    protected $casts = [
        'article_id' => 'int',
        'researcher_id' => 'int',
        'position' => 'int'
    ];

    protected $fillable = [
        'article_id',
        'researcher_id',
        'position'
    ];

    /**
     * Undocumented function
     *
     * @return void
     */
    public function affiliations()
    {
        return $this->belongsToMany(
            'App\Models\DataBase\Affiliation',
            '_authors_affiliations'
        )->using('App\Models\DataBase\AuhtorAffiliation');
    }

    /**
     * Undocumented function
     *
     * @param  Author  $item       The author that must be inserted
     * @param  Integer $position   The position of the author
     * @param  Integer $article_id The article the author is inserted on
     *
     * @return void
     */
    public static function insertAuthor($item, $position, $article_id)
    {
        if (!Researcher::where('first_name', '=', $item['first_name'])->where('last_name', '=', $item['last_name'])->exists()) {
            $researcher = [ 'first_name' => $item['first_name'],
                            'last_name'  => $item['last_name'],
                            'gender'     => $item['gender'],
            ];
            $researcher_id = DB::table('_researchers')->insertGetId($researcher);
        }
        
        $researcher_id = Researcher::where(
            'first_name',
            '=',
            $item['first_name']
        )->where(
            'last_name',
            '=',
            $item['last_name']
        )->first()->id;
        
        $author = [ 'article_id'    => $article_id,
                    'researcher_id' => $researcher_id,
                    'position'      => $position
        ];

        $author_id = DB::table('_authors')->insertGetId($author);
        
        if (isset($item['affiliation'])) {
            foreach ($item['affiliation'] as $affiliation) {
                if (!Affiliation::where('name', '=', $affiliation)->exists()) {
                    $affiliation =
                    [
                        'name' => $affiliation
                    ];
                    $affiliation_id = DB::table('_affiliations')->insertGetId($affiliation);
                    DB::table('_authors_affiliations')->insert(
                        [
                            'author_id'      => $author_id,
                            'affiliation_id' => $affiliation_id]
                    );
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param Authors[] $authors    List of authors to be inserted
     * @param Integer   $article_id The article the authors are inserted on
     *
     * @return void
     */
    public static function insertAuthors($authors, $article_id)
    {
        $i=1;
        foreach ($authors as $author) {
            try {
                Author::insertAuthor(
                    $author,
                    $i,
                    $article_id
                );
                $i++;
            } catch (QueryException $e) {
            }
        }
    }
}

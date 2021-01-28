<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class AuthorsAffiliation
 *
 * @property int $author_id
 * @property int $affiliation_id
 *
 * @package App\Models\DataBase
 */
class AuthorAffiliation extends Pivot
{
    protected $table = '_authors_affiliations';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'author_id' => 'int',
        'affiliation_id' => 'int'
    ];

    protected $fillable = [
        'author_id' => 'int',
        'affiliation_id' => 'int'
    ];
}

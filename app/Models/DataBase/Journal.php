<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Journal
 *
 * @property int $id
 * @property string $name
 * @property int $volume
 * @property int $number
 * @property string $pages
 *
 * @package App\Models\DataBase
 */
class Journal extends Model
{
    protected $table = '_journals';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'volume' => 'int',
        'number' => 'int'
    ];

    protected $fillable = [
        'id',
        'name',
        'volume',
        'number',
        'pages'
    ];
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function origine()
    {
        return $this->morphMany(
            'App\Models\DataBase\Origine',
            'journal'
        );
    }
}

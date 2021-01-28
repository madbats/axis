<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Colloque
 *
 * @property int $id
 * @property string $name
 * @property string $location
 * @property string $type
 *
 * @package App\Models\DataBase
 */
class Colloque extends Model
{
    protected $table = '_colloques';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'id' => 'int'
    ];

    protected $fillable = [
        'id',
        'name',
        'acronym',
        'url',
        'location',
        'type'
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
            'colloque'
        );
    }
}

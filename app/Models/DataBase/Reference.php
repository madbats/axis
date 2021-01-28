<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\DataBase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Reference
 *
 * @property string $citation
 * @property int $reference_id
 * @property int $citation_id
 *
 * @package App\Models\DataBase
 */
class Reference extends Pivot
{
    protected $table = '_references';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'reference_id' => 'int',
        'citation_id' => 'int'
    ];

    protected $fillable = [
        'citation',
        'reference_id',
        'citation_id'
    ];
}

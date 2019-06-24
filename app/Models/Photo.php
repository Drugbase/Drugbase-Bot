<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Photo
 *
 * @property int $id
 * @property int $drug_id
 * @property string $photo
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo whereDrugId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Photo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Photo extends Model
{
    protected $guarded = [];

    public function drug()
    {
        return $this->belongsTo('App\Models\Drug');
    }
}

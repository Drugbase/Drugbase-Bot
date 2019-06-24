<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



/**
 * App\Models\Drug
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $street_name
 * @property string|null $city
 * @property string|null $active_substance
 * @property string|null $symbol
 * @property string|null $state
 * @property string|null $color
 * @property string|null $inscription
 * @property string|null $shape
 * @property string|null $weight
 * @property string|null $weight_active
 * @property string|null $description
 * @property string|null $negative_effect
 * @property int $completed
 * @property int $confirm
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Photo[] $photos
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereActiveSubstance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereConfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereInscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereNegativeEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereShape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereStreetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Drug whereWeightActive($value)
 * @mixin \Eloquent
 */
class Drug extends Model
{
    protected $guarded = [];

    static $states = [
        0 => 'Жидкое',
        1 => 'Порошок',
        2 => 'Кристаллы',
        3 => 'Паста',
        4 => 'Таблетка',
        5 => 'Трава'
    ];

    static $active_substances = [
        0 => 'Амфетамин',
        1 => 'Метамфетамин',
        2 => 'MDMA',
        3 => 'Кетамин',
        4 => 'Кокаин',
        5 => 'Мефедрон',
        6 => 'Соли',
        7 => '2CB',
        8 => 'LSD',
        9 => 'NBOM',
        10 => 'Не знаю',
    ];

    static $colors = [
        0 => '⚪️ Белый',
        1 => '🌑 Серый',
        2 => '⚫️ Черный',
        3 => '🚪 Коричневый',
        4 => '🔴 Красный',
        5 => '🌸 Розовый',
        6 => '🏮️ Оранжевый',
        7 => '🌕 Желтый',
        8 => '🔶 Золотой',
        9 => '🍀 Зеленый',
        10 => '💎 Голубой',
        11 => '🔵 Синий',
        12 => '🔮 Фиолетовый',
        13 => '🌈 Разноцветный'
    ];

    static $shapes = [
        0 => '⭕️ Круг',
        1 => '➖ Продолговатая форма',
        2 => 'Овал',
        3 => '⬛️ Квадрат',
        4 => 'Прямоугольник',
        5 => '♦️ Ромб',
        6 => '🔺 3-х сторонний',
        7 => '5 сторон',
        8 => '6 сторон',
        9 => '7 сторон',
        10 => '🛑 8 сторон',
    ];

    public function photos()
    {
        return $this->hasMany('App\Models\Photo');
    }

    protected static function boot() {
        parent::boot();

        static::deleting(function($drug) {
            $photos_drug = Photo::whereDrugId($drug->id)->where('type', 0)->get();
            $photos_test = Photo::whereDrugId($drug->id)->where('type', 1)->get();

            if ($photos_drug) {
                foreach ($photos_drug as $photo) {
                    unlink(public_path('uploads/' . $photo->photo));
                }
            }

            if ($photos_test) {
                foreach ($photos_test as $photo) {
                    unlink(public_path('uploads/' . $photo->photo));
                }
            }
        });
    }
}

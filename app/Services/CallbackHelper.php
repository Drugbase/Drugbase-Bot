<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Telegram;

class CallbackHelper extends Model
{
    public function getActiveSubstance(User $user, Drug $drug, $telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $drug->active_substance = Drug::$active_substances[$callback_data[1]];
        $drug->save();

        $user->state = 'get_symbol';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Введите символ/название наркотика (если это таблетка)',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public function setPhotoDrug(User $user, Drug $drug, $telegram_id, $message_id)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $active_substances = Drug::$active_substances;

        $keyboard = [];
        $count = 0;
        $arr_number = 0;
        foreach ($active_substances as $key => $item) {
            $count++;
            if ($count % 2 == 1) {
                $keyboard[$arr_number][0] = [
                    'text' => $item,
                    'callback_data' => "get_active_substance@" . $key
                ];
            } else {
                $keyboard[$arr_number][1] = [
                    'text' => $item,
                    'callback_data' => "get_active_substance@" . $key
                ];
                $arr_number++;
            }
        }

        $keyboard[] = [[
            'text' => 'Другое',
            'callback_data' => "other@active_substance"
        ]];

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Выберете тип наркотика по активному веществу:',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => $keyboard,
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $user->state = 'get_active_substance';
        $user->save();
    }

    public static function getState(User $user, Drug $drug, $telegram_id, $message_id, $callback_data = null)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        if (isset($callback_data)) {
            $drug->state = Drug::$states[$callback_data[1]];
            $drug->save();
        }

        $user->state = 'get_color';
        $user->save();

        $colors = Drug::$colors;

        $keyboard = [];

        $keyboard[] = [[
            'text' => 'Пропустить',
            'callback_data' => "skip@color"
        ]];

        $count = 0;
        $arr_number = 1;
        foreach ($colors as $key => $item) {
            $count++;
            if ($count % 2 == 1) {
                $keyboard[$arr_number][0] = [
                    'text' => $item,
                    'callback_data' => "get_color@" . $key
                ];
            } else {
                $keyboard[$arr_number][1] = [
                    'text' => $item,
                    'callback_data' => "get_color@" . $key
                ];
                $arr_number++;
            }
        }

        $keyboard[] = [[
            'text' => 'Другое',
            'callback_data' => "other@color"
        ]];

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Выберите цвет наркотика:',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => $keyboard,
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getColor(User $user, Drug $drug, $telegram_id, $message_id, $callback_data = null)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        if (isset($callback_data)) {
            $drug->color = Drug::$colors[$callback_data[1]];
            $drug->save();
        }

        $user->state = 'get_inscription';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Введите надпись на таблетке (если это таблетка)',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [[
                            'text' => 'Пропустить',
                            'callback_data' => "skip@inscription"
                        ]]
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getShape(User $user, Drug $drug, $telegram_id, $message_id, $callback_data = null)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        if (isset($callback_data)) {
            $drug->shape = Drug::$shapes[$callback_data[1]];
            $drug->save();
        }

        $user->state = 'get_weight';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Введите общий вес таблетки',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [[
                            'text' => 'Пропустить',
                            'callback_data' => "skip@weight"
                        ]]
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getNegativeEffect(User $user, Drug $drug, $telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        if ($callback_data[1] == 'yes') {
            $user->state = 'get_negative_effect';
            $user->save();

            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => 'Опишите какой',
                    'reply_markup' => Telegram::replyKeyboardMarkup([
                        'inline_keyboard' => [
                            [[
                                'text' => 'Пропустить',
                                'callback_data' => "skip@negative_effect"
                            ]]
                        ],
                    ])
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}

        } elseif ($callback_data[1] == 'no') {
            TextHelper::getNegativeEffect($user, $drug, $telegram_id, $message_id);
        }
    }

    public function setPhotoTest(User $user, Drug $drug, $telegram_id, $message_id)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $drug->user_id = null;
        $drug->completed = true;
        $drug->save();

        try {
            $user->delete();
        } catch (\Exception $exception) { \Log::error($exception);}

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Спасибо! Ваш образец будет добавлен в базу после проверки модератором',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'keyboard' => [
                        ['Добавить наркотик'],
                        ['Поиск в базе']
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public function other(User $user, Drug $drug, $telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => '➡️ Другое',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        switch ($callback_data[1]) {
            case 'state':
                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Введите состояние',
                        'reply_markup' => Telegram::replyKeyboardMarkup([
                            'inline_keyboard' => [
                                [[
                                    'text' => 'Пропустить',
                                    'callback_data' => "skip@state"
                                ]]
                            ],
                        ])
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
                break;
            case 'color':
                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Введите цвет',
                        'reply_markup' => Telegram::replyKeyboardMarkup([
                            'inline_keyboard' => [
                                [[
                                    'text' => 'Пропустить',
                                    'callback_data' => "skip@color"
                                ]]
                            ],
                        ])
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
                break;
            case 'shape':
                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Введите форму',
                        'reply_markup' => Telegram::replyKeyboardMarkup([
                            'inline_keyboard' => [
                                [[
                                    'text' => 'Пропустить',
                                    'callback_data' => "skip@shape"
                                ]]
                            ],
                        ])
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
                break;
            case 'active_substance':
                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Введите тип наркотика по активному веществу',
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
                break;
        }
    }

    public function skip(User $user, Drug $drug, $telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => '➡️ Пропустить',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        switch ($callback_data[1]) {
            case 'state':
                TextHelper::getState($user, $drug, $telegram_id, $message_id);
                break;
            case 'color':
                self::getColor($user, $drug, $telegram_id, $message_id);
                break;
            case 'inscription':
                TextHelper::getInscription($user, $drug, $telegram_id, $message_id);
                break;
            case 'shape':
                self::getShape($user, $drug, $telegram_id, $message_id);
                break;
            case 'weight':
                TextHelper::getWeight($user, $drug, $telegram_id, $message_id);
                break;
            case 'weight_active':
                TextHelper::getWeightActive($user, $drug, $telegram_id, $message_id);
                break;
            case 'description':
                TextHelper::getDescription($user, $drug, $telegram_id, $message_id);
                break;
            case 'negative_effect':
                TextHelper::getNegativeEffect($user, $drug, $telegram_id, $message_id);
                break;
            case 'photo_test':
                self::setPhotoTest($user, $drug, $telegram_id, $message_id);
                break;
        }
    }

    public function findDrug($telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $text = '';
        $keyboard = [];

        $count = 0;
        $arr_number = 0;

        switch ($callback_data[1]) {
            case 'active_substance':
                $text = 'Выберите активное вещество';

                $active_substances = Drug::$active_substances;
                array_pop($active_substances);

                foreach ($active_substances as $key => $item) {
                    $count++;
                    if ($count % 2 == 1) {
                        $keyboard[$arr_number][0] = [
                            'text' => $item,
                            'callback_data' => "get_drug_by@active_substance@" . $key
                        ];
                    } else {
                        $keyboard[$arr_number][1] = [
                            'text' => $item,
                            'callback_data' => "get_drug_by@active_substance@" . $key
                        ];
                        $arr_number++;
                    }
                }

                $keyboard[] = [[
                    'text' => 'Другое',
                    'callback_data' => "get_drug_by@active_substance@other"
                ]];

                break;
            case 'color':
                $text = 'Выберите цвет';

                $colors = Drug::$colors;

                foreach ($colors as $key => $item) {
                    $count++;
                    if ($count % 2 == 1) {
                        $keyboard[$arr_number][0] = [
                            'text' => $item,
                            'callback_data' => "get_drug_by@color@" . $key
                        ];
                    } else {
                        $keyboard[$arr_number][1] = [
                            'text' => $item,
                            'callback_data' => "get_drug_by@color@" . $key
                        ];
                        $arr_number++;
                    }
                }

                $keyboard[] = [[
                    'text' => 'Другое',
                    'callback_data' => "get_drug_by@color@other"
                ]];

                break;
            case 'shape':
                $text = 'Выберите форму таблетки';

                $shapes = Drug::$shapes;

                foreach ($shapes as $key => $item) {
                    $count++;
                    if ($count % 2 == 1) {
                        $keyboard[$arr_number][0] = [
                            'text' => $item,
                            'callback_data' => "get_drug_by@shape@" . $key
                        ];
                    } else {
                        $keyboard[$arr_number][1] = [
                            'text' => $item,
                            'callback_data' => "get_drug_by@shape@" . $key
                        ];
                        $arr_number++;
                    }
                }

                $keyboard[] = [[
                    'text' => 'Другое',
                    'callback_data' => "get_drug_by@shape@other"
                ]];

                break;
        }

        if ($text != '') {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => $text,
                    'reply_markup' => Telegram::replyKeyboardMarkup([
                        'inline_keyboard' => $keyboard,
                    ])
                ]);
            } catch (\Exception $exception) {\Log::error($exception);}
        }
    }

    public function getDrug($telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $type = [];

        switch ($callback_data[1]) {
            case 'active_substance':
                $type = Drug::$active_substances;
                break;
            case 'color':
                $type = Drug::$colors;
                break;
            case 'shape':
                $type = Drug::$shapes;
                break;
        }

        if ($callback_data[2] == 'other') {
            $drugs = Drug::where('confirm', true)->whereNotIn($callback_data[1], $type)->get();
        } else {
            $drugs = Drug::where('confirm', true)->where($callback_data[1], $type[$callback_data[2]])->get();
        }

        if (count($drugs) == 0) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => 'Ничего не найдено',
                ]);
            } catch (\Exception $exception) {\Log::error($exception);}
        } else {

            /* @var $drug Drug */
            foreach ($drugs as $drug) {
                $photo = Photo::whereDrugId($drug->id)->where('type', 0)->first()->photo;

                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => "<a href=\"" . env('APP_URL')."uploads/" . $photo . "\">&#8204;</a>".
                            "<b>$drug->street_name</b>\n".
                            "\nГород " . $drug->city .
                            "\nАктивное вещество: " . $drug->active_substance,
                        'parse_mode' => 'HTML',
                        'reply_markup' => Telegram::replyKeyboardMarkup([
                            'inline_keyboard' => [
                                [[
                                    'text' => 'Больше информации',
                                    'callback_data' => "more_info@" . $drug->id
                                ]],
                            ],
                        ])
                    ]);
                } catch (\Exception $exception) {\Log::error($exception);}
            }
        }
    }

    public function moreInfo($telegram_id, $message_id, $callback_data)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        $drug = Drug::find($callback_data[1]);

        $drug_photos = Photo::whereDrugId($callback_data[1])->where('type', 0)->get();
        $test_photos = Photo::whereDrugId($callback_data[1])->where('type', 1)->get();

        if (count($drug_photos) > 0) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => 'Фото наркотика',
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}

            foreach ($drug_photos as $photo) {
                try {
                    Telegram::sendPhoto([
                        'chat_id' => $telegram_id,
                        'photo' => public_path('uploads/' . $photo->photo),
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
            }
        }

        if (count($test_photos) > 0) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => 'Фото теста',
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}

            foreach ($test_photos as $photo) {
                try {
                    Telegram::sendPhoto([
                        'chat_id' => $telegram_id,
                        'photo' => public_path('uploads/' . $photo->photo),
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
            }
        }

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Название: ' . $drug->street_name . "\n" .
                    'Город: ' . $drug->city  . "\n" .
                    'Активное вещество: ' . $drug->active_substance  . "\n" .
                    'Символ: ' . $drug->symbol  . "\n" .
                    ((isset($drug->state)) ? 'Состояние: ' . $drug->state  . "\n" : '') .
                    ((isset($drug->color)) ? 'Цвет: ' . $drug->color  . "\n" : '') .
                    ((isset($drug->inscription)) ? 'Надпись: ' . $drug->inscription  . "\n" : '') .
                    ((isset($drug->shape)) ? 'Форма: ' . $drug->shape  . "\n" : '') .
                    ((isset($drug->weight)) ? 'Вес: ' . $drug->weight  . "\n" : '') .
                    ((isset($drug->description)) ? 'Описание: ' . $drug->description  . "\n" : '') .
                    ((isset($drug->negative_effect)) ? 'Негативный эффект: ' . $drug->negative_effect  . "\n" : ''),
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }
}

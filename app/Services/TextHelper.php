<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Telegram;
use Telegram\Bot\Objects\Message;

class TextHelper extends Model
{
    public function addDrug(User $user, $telegram_id, $message_id)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id - 1,
            ]);
        } catch (\Exception $exception) {}

        try {
            Drug::whereUserId($user->id)->where('completed', false)->delete();
        } catch (\Exception $exception) { \Log::error($exception);}

        Drug::create([
            'user_id' => $user->id
        ]);

        $user->state = 'get_street_name';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Помните! Сравнивая образец вещества с информацией размещенной в нашей базе данных, нельзя быть уверенным в том, что ваш образец именно такой же по составу и по концентрации. Используя быстрые тесты, можно определить только наличие того или иного вещества в тестируемом образце, но не качество или чистоту вещества. Более темные цвета могут скрыть реакции на другие вещества, также содержащиеся в образце. Положительный или отрицательный тест на наличие вещества не означает, что наркотик безопасен. Употребление наркотиков никогда не является безопасным на 100%. Как действовать – решать вам.',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Введите «уличное» название',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public function findDrug(User $user, $telegram_id, $message_id)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id - 1,
            ]);
        } catch (\Exception $exception) {}

        try {
            Drug::whereUserId($user->id)->where('completed', false)->delete();
            $user->delete();
        } catch (\Exception $exception) {}

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Помните! Сравнивая образец вещества с информацией размещенной в нашей базе данных, нельзя быть уверенным в том, что ваш образец именно такой же по составу и по концентрации. Используя быстрые тесты, можно определить только наличие того или иного вещества в тестируемом образце, но не качество или чистоту вещества. Более темные цвета могут скрыть реакции на другие вещества, также содержащиеся в образце. Положительный или отрицательный тест на наличие вещества не означает, что наркотик безопасен. Употребление наркотиков никогда не является безопасным на 100%. Как действовать – решать вам.',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Выберите как хотите искать',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [[
                            'text' => 'Поиск по названию',
                            'switch_inline_query_current_chat' => "Название "
                        ]],
                        [[
                            'text' => 'Поиск по типу (по активному веществу)',
                            'callback_data' => "find_drug_by@active_substance"
                        ]],
                        [[
                            'text' => 'Поиск по городу (добавлено за последний месяц в этом городе)',
                            'switch_inline_query_current_chat' => "Город "
                        ]],
                        [[
                            'text' => 'Поиск по цвету',
                            'callback_data' => "find_drug_by@color"
                        ]],
                        [[
                            'text' => 'Поиск по форме',
                            'callback_data' => "find_drug_by@shape"
                        ]],
                        [[
                            'text' => 'Поиск по символу',
                            'switch_inline_query_current_chat' => "Символ "
                        ]],
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public function getDrug(User $user, $telegram_id, $message_id, $message_text)
    {
        $callback_data = explode('@', $user->state);

        $drugs = Drug::whereConfirm(true)->where($callback_data[1], 'like', "%$message_text%")->get();

        if(count($drugs) > 1) {
            foreach ($drugs as $drug) {
                self::oneDrug($drug, $telegram_id);
            }
        } elseif (count($drugs) == 1) {
            self::oneDrug($drugs->first(), $telegram_id);
        } else {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => 'К сожалению, я ничего не нашел. Попробуй еще раз, или поищи по другому критерию.',
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
            return;
        }

        try {
            $user->delete();
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    static function oneDrug(Drug $drug, $telegram_id)
    {
        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => '«Уличное» название: ' . $drug->street_name . "\n" .
                'Активное вещество: ' . $drug->active_substance . "\n" .
                'Символ: ' . $drug->symbol .
                (isset($drug->state) ? "\n" . 'Состояние: ' . $drug->state : '') .
                (isset($drug->color) ? "\n" . 'Цвет: ' . $drug->color : '') .
                (isset($drug->inscription) ? "\n" . 'Надпись: ' . $drug->inscription : '') .
                (isset($drug->shape) ? "\n" . 'Форма: ' . $drug->shape : '') .
                (isset($drug->weight) ? "\n" . 'Вес: ' . $drug->weight : '') .
                (isset($drug->description) ? "\n" . 'Описание: ' . $drug->description : '') .
                (isset($drug->negative_effect) ? "\n" . 'Негативный эффект: ' . $drug->negative_effect : ''),
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        if ($drug->photo_drug) {
            Telegram::sendPhoto([
                'chat_id' => $telegram_id,
                'photo' => public_path('uploads/' . $drug->photo_drug),
            ]);
        }

        if ($drug->photo_test) {
            Telegram::sendPhoto([
                'chat_id' => $telegram_id,
                'photo' => public_path('uploads/' . $drug->photo_test),
            ]);
        }
    }

    public function getStreetName(User $user, $drug, $message_text, $telegram_id)
    {
        $drug->street_name = $message_text;
        $drug->save();

        $user->state = 'get_city';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Введите город',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public function getCity(User $user, Drug $drug, $message_text, $telegram_id)
    {
        $drug->city = $message_text;
        $drug->save();

        $user->state = 'get_photo_drug';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Добавьте фотографию наркотика (если это таблетка - фото с двух сторон)',
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public function getPhotoDrug(User $user, Drug $drug, Message $message, $telegram_id)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message->getMessageId() - 1,
            ]);
        } catch (\Exception $exception) {}

        if ($photo = $message->get('photo')) {

            $number = count($photo) - 1;
            $path = 'uploads/images/' . $photo[$number]['file_id'] . '.jpg';
            $result = self::downloadFile($path, $photo[$number]['file_id']);

            if (!$result) {
                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Ошибка! Не удалось отправить фото. Попробуйте еще раз',
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
            } else {
                Photo::create([
                    'drug_id' => $drug->id,
                    'photo' => 'images/' . $photo[$number]['file_id'] . '.jpg',
                    'type' => 0
                ]);

                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Отправьте еще одно фото, или нажмите далее',
                        'reply_markup' => Telegram::replyKeyboardMarkup([
                            'inline_keyboard' => [
                                [[
                                    'text' => 'Далее',
                                    'callback_data' => 'set_photo_drug'
                                ]]
                            ],
                        ])
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
            }
        } else {
            try {
                Telegram::sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => 'Отправьте фотографию',
                    'reply_markup' => Telegram::replyKeyboardMarkup([
                        'inline_keyboard' => [
                            [[
                                'text' => 'Далее',
                                'callback_data' => 'set_photo_drug'
                            ]]
                        ],
                    ])
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
        }
    }

    public static function getActiveSubstance(User $user, Drug $drug, $telegram_id, $message_text)
    {
        $drug->active_substance = $message_text;
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

    public function getSymbol(User $user, Drug $drug, $message_text, $telegram_id)
    {
        $drug->symbol = $message_text;
        $drug->save();

        $user->state = 'get_state';
        $user->save();

        $states = Drug::$states;

        $keyboard = [];

        $keyboard[] = [[
            'text' => 'Пропустить',
            'callback_data' => "skip@state"
        ]];

        $count = 0;
        $arr_number = 1;
        foreach ($states as $key => $item) {
            $count++;
            if ($count % 2 == 1) {
                $keyboard[$arr_number][0] = [
                    'text' => $item,
                    'callback_data' => "get_state@" . $key
                ];
            } else {
                $keyboard[$arr_number][1] = [
                    'text' => $item,
                    'callback_data' => "get_state@" . $key
                ];
                $arr_number++;
            }
        }

        $keyboard[] = [[
            'text' => 'Другое',
            'callback_data' => "other@state"
        ]];

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Выберите состояние:',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => $keyboard,
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getState(User $user, Drug $drug, $telegram_id, $message_id, $message_text = null)
    {
        if (isset($message_text)) {
            try {
                Telegram::editMessageReplyMarkup([
                    'chat_id' => $telegram_id,
                    'message_id' => $message_id - 1,
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}

            $drug->state = $message_text;
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

    public static function getColor(User $user, Drug $drug, $telegram_id, $message_text)
    {
        $drug->color = $message_text;
        $drug->save();

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

    public static function getInscription(User $user, Drug $drug, $telegram_id, $message_id, $message_text = null)
    {
        if (isset($message_text)) {
            try {
                Telegram::editMessageReplyMarkup([
                    'chat_id' => $telegram_id,
                    'message_id' => $message_id - 1,
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
            $drug->inscription = $message_text;
            $drug->save();
        }

        $user->state = 'get_shape';
        $user->save();

        $shapes = Drug::$shapes;

        $keyboard[] = [[
            'text' => 'Пропустить',
            'callback_data' => "skip@shape"
        ]];

        $count = 0;
        $arr_number = 1;
        foreach ($shapes as $key => $item) {
            $count++;
            if ($count % 2 == 1) {
                $keyboard[$arr_number][0] = [
                    'text' => $item,
                    'callback_data' => "get_shape@" . $key
                ];
            } else {
                $keyboard[$arr_number][1] = [
                    'text' => $item,
                    'callback_data' => "get_shape@" . $key
                ];
                $arr_number++;
            }
        }

        $keyboard[] = [[
            'text' => 'Другое',
            'callback_data' => "other@shape"
        ]];

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Выберите форму таблетки:',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => $keyboard,
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getShape(User $user, Drug $drug, $telegram_id, $message_text)
    {
        $drug->shape = $message_text;
        $drug->save();

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

    public static function getWeight(User $user, Drug $drug, $telegram_id, $message_id, $message_text = null)
    {
        if (isset($message_text)) {
            try {
                Telegram::editMessageReplyMarkup([
                    'chat_id' => $telegram_id,
                    'message_id' => $message_id - 1,
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
            $drug->weight = $message_text;
            $drug->save();
        }

        $user->state = 'get_weight_active';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Введите количество действующего вещества таблетки (mg)',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [[
                            'text' => 'Пропустить',
                            'callback_data' => "skip@weight_active"
                        ]]
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getWeightActive(User $user, Drug $drug, $telegram_id, $message_id, $message_text = null)
    {
        if (isset($message_text)) {
            try {
                Telegram::editMessageReplyMarkup([
                    'chat_id' => $telegram_id,
                    'message_id' => $message_id - 1,
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
            $drug->weight_active = $message_text;
            $drug->save();
        }

        $user->state = 'get_description';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Добавьте описание',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [[
                            'text' => 'Пропустить',
                            'callback_data' => "skip@description"
                        ]]
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getDescription(User $user, Drug $drug, $telegram_id, $message_id, $message_text = null)
    {
        if (isset($message_text)) {
            try {
                Telegram::editMessageReplyMarkup([
                    'chat_id' => $telegram_id,
                    'message_id' => $message_id - 1,
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
            $drug->description = $message_text;
            $drug->save();
        }

        $user->state = 'get_answer_negative_effect';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Был ли негативный эффект от употребления? ',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Да',
                                'callback_data' => "get_negative_effect@yes"
                            ],
                            [
                                'text' => 'Нет',
                                'callback_data' => "get_negative_effect@no"
                            ]
                        ],
                        [[
                            'text' => 'Пропустить',
                            'callback_data' => "skip@negative_effect"
                        ]]
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getNegativeEffect(User $user, Drug $drug, $telegram_id, $message_id, $message_text = null)
    {
        if (isset($message_text)) {
            try {
                Telegram::editMessageReplyMarkup([
                    'chat_id' => $telegram_id,
                    'message_id' => $message_id - 1,
                ]);
            } catch (\Exception $exception) { \Log::error($exception);}
            $drug->negative_effect = $message_text;
            $drug->save();
        }

        $user->state = 'get_photo_test';
        $user->save();

        try {
            Telegram::sendMessage([
                'chat_id' => $telegram_id,
                'text' => 'Добавьте фотографию теста',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => [
                        [[
                            'text' => 'Пропустить',
                            'callback_data' => "skip@photo_test"
                        ]]
                    ],
                ])
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}
    }

    public static function getPhotoTest(User $user, Drug $drug, $telegram_id, $message_id, Message $message = null)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $message_id - 1,
            ]);
        } catch (\Exception $exception) { \Log::error($exception);}

        if (isset($message)) {
            if ($photo = $message->get('photo')) {

                $number = count($photo) - 1;
                $path = 'uploads/images/' . $photo[$number]['file_id'] . '.jpg';
                $result = self::downloadFile($path, $photo[$number]['file_id']);

                if (!$result) {
                    try {
                        Telegram::sendMessage([
                            'chat_id' => $telegram_id,
                            'text' => 'Ошибка! Не удалось отправить фото. Попробуйте еще раз',
                        ]);
                    } catch (\Exception $exception) { \Log::error($exception);}
                    return true;
                } else {
                    Photo::create([
                        'drug_id' => $drug->id,
                        'photo' => 'images/' . $photo[$number]['file_id'] . '.jpg',
                        'type' => 1
                    ]);

                    try {
                        Telegram::sendMessage([
                            'chat_id' => $telegram_id,
                            'text' => 'Отправьте еще одно фото, или нажмите далее',
                            'reply_markup' => Telegram::replyKeyboardMarkup([
                                'inline_keyboard' => [
                                    [[
                                        'text' => 'Далее',
                                        'callback_data' => 'set_photo_test'
                                    ]]
                                ],
                            ])
                        ]);
                    } catch (\Exception $exception) { \Log::error($exception);}
                    return true;
                }
            } else {
                try {
                    Telegram::sendMessage([
                        'chat_id' => $telegram_id,
                        'text' => 'Отправьте фотографию',
                        'reply_markup' => Telegram::replyKeyboardMarkup([
                            'inline_keyboard' => [
                                [[
                                    'text' => 'Далее',
                                    'callback_data' => 'set_photo_test'
                                ]]
                            ],
                        ])
                    ]);
                } catch (\Exception $exception) { \Log::error($exception);}
                return true;
            }
        }
    }





    private static function downloadFile($path, $file_id) {
        try {
            $client = new \GuzzleHttp\Client();

            $telegram = Telegram::getFile(['file_id' => $file_id]);

            $client->request('GET', "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . '/' . $telegram['file_path'], [
                'sink' => $path
            ]);

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}

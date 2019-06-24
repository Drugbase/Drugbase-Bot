<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Telegram;

class CallbackInlineHelper extends Model
{
    static function moreInfo($telegram_id, $inline_message_id, $drug_id)
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id' => $telegram_id,
                'message_id' => $inline_message_id,
            ]);
        } catch (\Exception $exception) {}

        $drug = Drug::find($drug_id);

        $drug_photos = Photo::whereDrugId($drug_id)->where('type', 0)->get();
        $test_photos = Photo::whereDrugId($drug_id)->where('type', 1)->get();

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

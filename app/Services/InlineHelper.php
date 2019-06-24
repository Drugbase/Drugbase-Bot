<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Telegram;

class InlineHelper extends Model
{
    public function search($query_id, $search, $name, $telegram_id)
    {
        $name = trim($name);

        if ($search == 'Название') {
            $drugs = Drug::whereConfirm(true)->where('street_name', 'like', "%$name%")->get();
        } elseif ($search == 'Город') {
            $drugs = Drug::whereConfirm(true)->where('city', 'like', "%$name%")->get();
        } elseif ($search == 'Символ') {
            $drugs = Drug::whereConfirm(true)->where('symbol', 'like', "%$name%")->get();
        } else {
            $drugs = Drug::whereConfirm(true)->get();
        }

        $results = [];

        foreach ($drugs as $drug) {

            $photo = Photo::whereDrugId($drug->id)->where('type', 0)->first()->photo;

            $results[] = [
                'type' => 'article',
                'id' => $drug->id,
                'thumb_url' => env('APP_URL')."uploads/" . $photo,
                'title' => 'Название: ' . $drug->street_name,
                'description' => 'Город: ' . $drug->city . "\n" . 'Символ: ' . $drug->symbol,
                'input_message_content' => [
                    'message_text' => "<a href=\"" . env('APP_URL')."uploads/" . $photo . "\">&#8204;</a>".
                        "<b>$drug->street_name</b>\n".
                        "\nГород " . $drug->city .
                        "\nАктивное вещество: " . $drug->active_substance,
                    'parse_mode' => 'HTML',
                ],
                'reply_markup' => [
                    'inline_keyboard' => [
                        [[
                            'text' => 'Больше информации',
                            'callback_data' => "more_info@" . $drug->id
                        ]],
                    ],
                ]
            ];
        }

        try {
            \Telegram::answerInlineQuery([
                'inline_query_id' => $query_id,
                'results' => json_encode($results),
                'cache_time' => 0
            ]);
        } catch (\Exception $e) {}
    }
}
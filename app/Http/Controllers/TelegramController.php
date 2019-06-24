<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Photo;
use App\Models\User;
use App\Services\CallbackHandler;
use App\Services\CallbackHelper;
use App\Services\CallbackInlineHelper;
use App\Services\InlineHandler;
use App\Services\InlineHelper;
use App\Services\TextHandler;
use App\Services\TextHelper;

class TelegramController extends Controller
{
    public function webhook(\Telegram\Bot\Objects\Update $telegram_updates)
    {
        if (isset($telegram_updates['message']['entities'][0]['type'])
            && $telegram_updates['message']['entities'][0]['type'] == 'bot_command'
            && $telegram_updates['message']['text'] == '/start') {
            return;
        }

        if (isset($telegram_updates['message'])) {
            $message = $telegram_updates['message'];
            $telegram_id = $message['chat']['id'];
            $message_id = $message['message_id'];
            if (isset($telegram_updates['message']['text'])) {
                $message_text = $telegram_updates['message']['text'];
            }

            $text_helper = new TextHelper;
            TextHandler::index($telegram_id, $message_id, $text_helper, $message, $message_text ?? null);

        } elseif (isset($telegram_updates['callback_query']['message'])) {

            $message = $telegram_updates['callback_query']['message'];
            $telegram_id = $message['chat']['id'];
            $message_id = $message['message_id'];
            $callback = $telegram_updates['callback_query'];
            $callback_data = explode('@', $callback['data']);

            $user = User::whereTelegramId($telegram_id)->first();

            $callback_helper = new CallbackHelper;
            CallbackHandler::index($telegram_id, $message_id, $callback_data, $callback_helper, $user ?? null);

        } elseif (isset($telegram_updates['inline_query'])) {

            $query_id = $telegram_updates['inline_query']['id'];
            $query = $telegram_updates['inline_query']['query'];
            $telegram_id = $telegram_updates['inline_query']['from']['id'];

            $inline_helper = new InlineHelper();

            InlineHandler::index($inline_helper, $query_id, $query, $telegram_id);

        } elseif (isset($telegram_updates['callback_query']['data'])) {

            $telegram = $telegram_updates['callback_query'];
            $telegram_id = $telegram['from']['id'];
            $inline_message_id = $telegram['inline_message_id'];
            $callback_data = explode('@', $telegram['data']);

            CallbackInlineHelper::moreInfo($telegram_id, $inline_message_id, $callback_data[1]);

        }
    }
}

<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Objects\Message;

class TextHandler extends Model
{
    static function index($telegram_id, $message_id, TextHelper $text_helper, Message $message, $message_text)
    {
        $user = User::whereTelegramId($telegram_id)->first();

        if ($message_text == 'Добавить наркотик' || $message_text == 'Поиск в базе'){
            if (!$user) {
                $user = new User;
                $user->telegram_id = $telegram_id;
            }
            $user->save();

            if ($message_text == 'Добавить наркотик') {
            $text_helper->addDrug($user, $telegram_id, $message_id);
            } elseif ($message_text == 'Поиск в базе') {
                $text_helper->findDrug($user, $telegram_id, $message_id);
            }

            return;
        }

        if (!$user) return;

        $drug = Drug::whereUserId($user->id)->first();

        if ($user->state == 'get_street_name') {
            $text_helper->getStreetName($user, $drug, $message_text, $telegram_id);

        } elseif ($user->state == 'get_city') {
            $text_helper->getCity($user, $drug, $message_text, $telegram_id);

        } elseif ($user->state == 'get_photo_drug') {
            $text_helper->getPhotoDrug($user, $drug, $message, $telegram_id);

        } elseif ($user->state == 'get_active_substance') {
            $text_helper->getActiveSubstance($user, $drug, $telegram_id, $message_text);

        } elseif ($user->state == 'get_symbol') {
            $text_helper->getSymbol($user, $drug, $message_text, $telegram_id);

        } elseif ($user->state == 'get_state') {
            $text_helper->getState($user, $drug, $telegram_id, $message_id, $message_text);

        } elseif ($user->state == 'get_color') {
            $text_helper->getColor($user, $drug, $telegram_id, $message_text);

        } elseif ($user->state == 'get_inscription') {
            $text_helper->getInscription($user, $drug, $telegram_id, $message_id, $message_text);

        } elseif ($user->state == 'get_shape') {
            $text_helper->getShape($user, $drug, $telegram_id, $message_text);

        } elseif ($user->state == 'get_weight') {
            $text_helper->getWeight($user, $drug, $telegram_id, $message_id, $message_text);

        } elseif ($user->state == 'get_weight_active') {
            $text_helper->getWeightActive($user, $drug, $telegram_id, $message_id, $message_text);

        } elseif ($user->state == 'get_description') {
            $text_helper->getDescription($user, $drug, $telegram_id, $message_id, $message_text);

        } elseif ($user->state == 'get_negative_effect') {
            $text_helper->getNegativeEffect($user, $drug, $telegram_id, $message_id, $message_text);

        } elseif ($user->state == 'get_photo_test') {
            $text_helper->getPhotoTest($user, $drug, $telegram_id, $message_id, $message);

        } elseif (preg_match("/find_drug/i", $user->state)) {
            $text_helper->getDrug($user, $telegram_id, $message_id, $message_text);
        }
    }
}

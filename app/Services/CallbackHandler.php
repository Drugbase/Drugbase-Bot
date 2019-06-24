<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Objects\Message;

class CallbackHandler extends Model
{
    static function index($telegram_id, $message_id, $callback_data, CallbackHelper $callback_helper, User $user = null)
    {
        if ($callback_data[0] == 'find_drug_by') {
            $callback_helper->findDrug($telegram_id, $message_id, $callback_data);
            return;

        } elseif ($callback_data[0] == 'get_drug_by') {
            $callback_helper->getDrug($telegram_id, $message_id, $callback_data);

            return;
        } elseif ($callback_data[0] == 'more_info') {
            $callback_helper->moreInfo($telegram_id, $message_id, $callback_data);

            return;
        }

        if (!isset($user)) return;

        $drug = Drug::whereUserId($user->id)->first();

        if ($callback_data[0] == 'get_active_substance' && $user->state == 'get_active_substance') {
            $callback_helper->getActiveSubstance($user, $drug, $telegram_id, $message_id, $callback_data);

        } elseif ($callback_data[0] == 'set_photo_drug' && $user->state == 'get_photo_drug') {
            $callback_helper->setPhotoDrug($user, $drug, $telegram_id, $message_id);

        } elseif ($callback_data[0] == 'get_state' && $user->state == 'get_state') {
            $callback_helper->getState($user, $drug, $telegram_id, $message_id, $callback_data);

        } elseif ($callback_data[0] == 'get_color' && $user->state == 'get_color') {
            $callback_helper->getColor($user, $drug, $telegram_id, $message_id, $callback_data);

        } elseif ($callback_data[0] == 'get_shape' && $user->state == 'get_shape') {
            $callback_helper->getShape($user, $drug, $telegram_id, $message_id, $callback_data);

        } elseif ($callback_data[0] == 'get_negative_effect' && $user->state == 'get_answer_negative_effect') {
            $callback_helper->getNegativeEffect($user, $drug, $telegram_id, $message_id, $callback_data);

        } elseif ($callback_data[0] == 'set_photo_test' && $user->state == 'get_photo_test') {
            $callback_helper->setPhotoTest($user, $drug, $telegram_id, $message_id);

        } elseif ($callback_data[0] == 'other') {
            $callback_helper->other($user, $drug, $telegram_id, $message_id, $callback_data);

        } elseif ($callback_data[0] == 'skip') {
            $callback_helper->skip($user, $drug, $telegram_id, $message_id, $callback_data);

        }
    }
}

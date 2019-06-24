<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class InlineHandler extends Model
{
    static function index(InlineHelper $inline_helper, $query_id, $query, $telegram_id)
    {
        if (preg_match("#Название(.*)#", $query, $search)) {
            $inline_helper->search($query_id, 'Название', $search[1], $telegram_id);

        } elseif (preg_match("#Город(.*)#", $query, $search)) {
            $inline_helper->search($query_id, 'Город', $search[1], $telegram_id);

        } elseif (preg_match("#Символ(.*)#", $query, $search)) {
            $inline_helper->search($query_id, 'Символ', $search[1], $telegram_id);

        } else {
            $inline_helper->search($query_id, '' ,'', $telegram_id);
        }
    }
}

<?php

namespace App\TelegramCommands;

use App\Models\Drug;
use App\Models\User;
use function GuzzleHttp\Promise\queue;
use Telegram\Bot\Commands\Command;
use Telegram;


/**
 * Class StartCommand.
 */
class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var array Command Aliases
     */
    protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected $description = 'Start command';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $telegram = Telegram::getWebhookUpdates()['message'];
        $telegram_id = $telegram['chat']['id'];

        $user = User::whereTelegramId($telegram_id)->first();

        if ($user) {
            $drug = Drug::whereUserId($user->id)->where('completed', false)->first();

            if ($drug) {
                $file_path_drug = Drug::whereUserId($user->id)->where('completed', false)->first()->photo_drug;
                $file_path_test = Drug::whereUserId($user->id)->where('completed', false)->first()->photo_test;

                try {
                    if (isset($file_path_drug)) unlink('uploads/' .  $file_path_drug);
                    if (isset($file_path_test)) unlink('uploads/' .  $file_path_test);
                    $drug->delete();
                } catch (\Exception $exception) { \Log::error($exception);}
            }

            try {
                $user->delete();
            } catch (\Exception $exception) { \Log::error($exception);}
        }

        try {
            $this->replyWithMessage([
                'text' => 'Этот бот собирает информацию об уличных наркотиках с целью снижения вреда от их употребления. 
Каждый может добавить сюда фото, описание, полезную информацию и информацию о рисках связанных с употреблением конкретного вещества.  Бот позволяет сделать поиск по существующей базе наркотиков.
Никакая информация о пользователях не собирается и не сохраняется для обеспечения анонимности источника информации. Для того, чтобы вы могли убедиться в этом, мы выкладываем исходный код.',
                'reply_markup' => Telegram::replyKeyboardMarkup([
                    'keyboard' => [
                        ['Добавить наркотик'],
                        ['Поиск в базе']
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false
                ])
            ]);
        } catch (\Exception $exception) {\Log::error($exception);}

        try {
            Telegram::sendDocument([
                'chat_id' => $telegram_id,
                'document' => storage_path('bot.zip'),
            ]);
        } catch (\Exception $exception) {\Log::error($exception);}

        try {
            $this->replyWithMessage([
                'text' => 'Помните! Сравнивая образец вещества с информацией размещенной в нашей базе данных, нельзя быть уверенным в том, что ваш образец именно такой же по составу и по концентрации. Используя быстрые тесты, можно определить только наличие того или иного вещества в тестируемом образце, но не качество или чистоту вещества. Более темные цвета могут скрыть реакции на другие вещества, также содержащиеся в образце. Положительный или отрицательный тест на наличие вещества не означает, что наркотик безопасен. Употребление наркотиков никогда не является безопасным на 100%. Как действовать – решать вам.',
            ]);
        } catch (\Exception $exception) {\Log::error($exception);}
    }
}

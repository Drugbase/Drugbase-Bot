<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/telegram/setWebhook', function () {

    $webhookUrl = env('APP_URL') . 'api/telegram/webhook';
    try {
        $api = new \Telegram\Bot\Api(env('TELEGRAM_BOT_TOKEN'));
        $api->setWebhook([
            'url' => $webhookUrl,
        ]);
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

    dd($api->getWebhookInfo([]));
});

Route::post('/telegram/webhook', function () {

    $update = Telegram::getWebhookUpdates();

    $api = new \Telegram\Bot\Api(env('TELEGRAM_BOT_TOKEN'));
    $api->addCommand(\App\TelegramCommands\StartCommand::class);
    $api->commandsHandler(true);

    if (!empty($update)) {
        $telegram = new \App\Http\Controllers\TelegramController;
        $telegram->webhook($update);
    }

    return 'ok';
});

<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'not post']);
    exit;
}

$name    = strip_tags($_POST['name']    ?? 'Не указано');
$phone   = strip_tags($_POST['phone']   ?? '');
$service = strip_tags($_POST['service'] ?? 'Не указано');
$comment = strip_tags($_POST['comment'] ?? '');

// Лог всех входящих запросов
$log = date('d.m.Y H:i:s') . " | phone=$phone | service=$service\n";
file_put_contents(__DIR__ . '/leads.log', $log, FILE_APPEND);

if (!$phone) {
    echo json_encode(['ok' => false, 'error' => 'no phone']);
    exit;
}

$bot_token = '8576830207:AAHIwPhzmzDCvu42A0uCWuu7eerFeImmXkY';
$chat_id   = '6120365862';

$text  = "🔥 *Новая заявка с сайта*\n\n";
$text .= "👤 Имя: " . $name . "\n";
$text .= "📞 Телефон: " . $phone . "\n";
$text .= "🔧 Услуга: " . $service . "\n";
if ($comment) $text .= "💬 Комментарий: " . $comment . "\n";
$text .= "\n⏰ " . date('d.m.Y H:i');

$url = "https://api.telegram.org/bot$bot_token/sendMessage";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'chat_id'    => $chat_id,
    'text'       => $text,
    'parse_mode' => 'Markdown',
]);
$result = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

$response = json_decode($result, true);
$ok = $response['ok'] ?? false;

file_put_contents(__DIR__ . '/leads.log', "  tg_result=$result | curl_err=$curl_error\n", FILE_APPEND);

echo json_encode(['ok' => $ok]);

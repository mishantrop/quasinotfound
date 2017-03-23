<?php
/**
 * @author <https://quasi-art.ru>
 * @version 1.3
 * 19.06.2015 - 11.12.2016
 */

/**
 * Настройки
 */
// Режим отладки
$debug = false;
// Записывать ли сообщения в журнал ошибок
$log = false;
// Отправлять ли почту
$sendEmail = true;
// На какой адрес прислать письмо
$email = 'site@ya.ru';
$emailFrom = 'admin@site.ru';
$emailFromName = 'quasiNotFound';
// Массив исключений
// Например, '/favicon.ico'
$excludeUri = [
	'/favicon.ico',
];

// Запись отладочной информации
if (!function_exists('quasiNotFoundWriteDebug')) {
	function quasiNotFoundWriteDebug($text) {
		global $debug;
		global $modx;
		if ($debug) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, $text);
		}
	}
}

// Запись информации в журнал
if (!function_exists('quasiNotFoundWriteLog')) {
	function quasiNotFoundWriteLog($text) {
		global $log;
		global $modx;
		if ($log) {
			$modx->log(xPDO::LOG_LEVEL_ERROR, $text);
		}
	}
}

// Плагин не нужен в админке, наверное
if ($modx->context->key == 'mgr') {
	return;
}

// Время запроса
$date = date('H:i:s d.m.Y');
// Адрес страницы
$uri = $modx->getOption('REQUEST_URI', $_SERVER, '/');
// Адрес сайта
$host = $modx->getOption('HTTP_HOST', $_SERVER, '');
// Откуда посетитель пришёл
$referer = $modx->getOption('HTTP_REFERER', $_SERVER, '');
// Браузер
$ua = $modx->getOption('HTTP_USER_AGENT', $_SERVER, '');
// IP
$ip = $modx->getOption('REMOTE_ADDR', $_SERVER, '');
// Полный адрес запрашиваемой страницы
$url = $host.$uri;
// Тема письма
$subject = 'Error 404 on '.$host;

// Если запрос в списке исключений, то не нужно ничего записывать и отправлять
if (in_array($uri, $excludeUri)) {
	quasiNotFoundWriteDebug('Uri "'.$uri.'" in black list');
	return;
}

// Сообщение для письма
$message = 'Resource: '.$url.' not found; <br/>';
$message .= 'Date: '.$date.'<br/>';
$message .= (!empty($referer)) ? 'Referer: '.$referer.'<br/>' : '';
$message .= 'User-agent: '.$ua.'<br/>';
$message .= 'IP: '.$ip.'<br>';
$message = '<html><body>'.$message.'</body></html>';

if ($log) {
	// Сообщение для журнала
	$messageLog = 'quasiNotFound; ';
	$messageLog .= 'Page not found: '.$url.'; ';
	$messageLog .= 'Date: '.$date.'; ';
	$messageLog .= (!empty($referer)) ? 'Referer: '.$referer.'; ': '';
	$messageLog .= 'User-agent: '.$ua.'; ';
	$messageLog .= 'IP: '.$ip.'; ';
	quasiNotFoundWriteLog($messageLog);
}

// Если нужно отправить письмо
if ($sendEmail && !empty($email)) {
    $modx->getService('mail', 'mail.modPHPMailer');
    $modx->mail->setHTML(true);
    $modx->mail->set(modMail::MAIL_BODY, $message);
    $modx->mail->set(modMail::MAIL_FROM, $emailFrom);
    $modx->mail->set(modMail::MAIL_FROM_NAME, $emailFromName);
    $modx->mail->set(modMail::MAIL_SUBJECT, $subject);
    $modx->mail->address('to', $email);
    $modx->mail->address('reply-to', $emailFrom);
    if ($modx->mail->send()) {
		quasiNotFoundWriteDebug('Email was sent successfully');
	} else {
		quasiNotFoundWriteDebug('Error on send email');
	}
} elseif ($sendEmail && empty($email)) {
	quasiNotFoundWriteDebug('Email address is empty');
}
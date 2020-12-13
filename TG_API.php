<?php
	class TelegramBot {
		private $TOKEN;
		function __construct($_TOKEN) {
			$this->TOKEN = $_TOKEN;
		}

		function sendMessage($chat_id, $message, $reply_markup="") {
			$smsg = 'https://api.telegram.org/bot' . $this->TOKEN . '/sendMessage' .
					'?chat_id='      . $chat_id .
					'&text='         . urlencode($message) .
					'&reply_markup=' . $reply_markup;

			file_get_contents($smsg);
		}

		function answerCallbackQuery($callback_query_id, $text, $show_alert=false) {
			$acq = 'https://api.telegram.org/bot' . $this->TOKEN . '/answerCallbackQuery' .
				   '?callback_query_id=' . $callback_query_id .
				   '&text='              . $text .
				   '&show_alert='        . $show_alert;

			file_get_contents($acq);
		}

		function editMessageText($chat_id, $message_id, $text, $reply_markup="") {
			$emt = 'https://api.telegram.org/bot' . $this->TOKEN . '/editMessageText' .
				   '?chat_id=' 	   . $chat_id .
				   '&message_id='  . $message_id .
				   '&text=' 	   . $text .
				   '&reply_markup=' . $reply_markup;
			
			file_get_contents($emt);
		}

		function setCommandList($commandsJSON) {
			$scl = 'https://api.telegram.org/bot' . $this->TOKEN . '/setMyCommands' .
				   '?commands=' . json_encode($commandsJSON);

			file_get_contents($scl);
		}
	}
?>
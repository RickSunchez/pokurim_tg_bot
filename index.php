<?php
	include "TG_API.php";
	include "php/db_link.php";
	include "php/user_class.php";

	$file = file_get_contents("secret.json");
	$secret = json_decode($file, true);
	$bot = new TelegramBot($secret["api_key"]);

	$_data = file_get_contents("php://input");
	$data = json_decode($_data, true);

	$chat_id = $data["message"]["chat"]["id"];
	$text    = $data["message"]["text"];

	$user = new User($chat_id, $link);

	if ($user->action == "on_start") {
		$bot->sendMessage($chat_id, "Приветствую, если ты здесь, значит ты дошел до той точки, когда понял, что курение занимает слишком много места в твоей жизни. Я не обещаю, что ты бросишь курить, но давай попробуем нормализовать этот процесс. Итак, правила просты: покурил - нажми кнопку. Между перекурами есть перерыв, покурил внеочереди - нехороший человек, но осуждать тебя некому, кроме себя самого. Итак, прежде чем начать, давай настроим время между перекурами (в минутах):");
		$user->set_action("pause_set");
	} elseif ($user->action == "pause_set") {
		if ((int)$text == 0) {
			$bot->sendMessage($chat_id, "Что-то пошло не так. Введите только цифры, без букв и пробелов");
		} else {
			$pause = (int)$text;
			$bot->sendMessage($chat_id, "Время перерыва: $pause мин. Помни одно простое правило: покурил - нажми кнопку, иначе ничего не получится, удачи!", big_blue_button());

			$user->set_pause($pause);
			$user->set_action("on_game");
		}
	} elseif ($user->action == "on_game") {
		if ($text == "Покурил") {
			$last_smoke = $user->get_last();

			if (!$last_smoke) {
				$user->add_event();
				$bot->sendMessage($chat_id, 
					"Это Ваш первый сеанс. Следующий через " . $user->pause . "мин.", big_blue_button());
			} else {
				$diff = time() - $last_smoke;
				$user->add_event();

				if ($diff >= $user->pause*60) {
					$message = "Так держать!";
				} else {
					$message = "Сорвался. Соберись! У тебя все получится!";
				}

				$bot->sendMessage(
					$chat_id, 
					$message . 
						"\n\nСледующий сеанс через " . 
						$user->pause . 
						"мин.\n" .
						"Количество сеансов: " . $user->get_count(),
					big_blue_button()
				);
			}
			$user->set_msg(0);
		}
	}

	function big_blue_button() {
		$keyboard = [
			['Покурил']
		];

		$rm = array(
			'keyboard' => $keyboard,
			'resize_keyboard' => false
		);

		return json_encode($rm);
	}
?>
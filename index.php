<?php
	include "TG_API.php";

	$file = file_get_contents("secret.json");
	$secret = json_decode($file, true);
	$bot = new TelegramBot($secret["api_key"]);

	$_data = file_get_contents("php://input");
	$data = json_decode($_data, true);

	$from = $data["message"]["from"]["id"];
	$chat_id = $data["message"]["chat"]["id"];
	$text = $data["message"]["text"];

	$user_data = check_user($from, $chat_id);

	if ($user_data["action"] == "on_start") {
		$bot->sendMessage($chat_id, "Приветствую, если ты здесь, значит ты дошел до той точки, когда понял, что курение занимает слишком много места в твоей жизни. Я не обещаю, что ты бросишь курить, но давай попробуем нормализовать этот процесс. Итак, правила просты: покурил - нажми кнопку. Между перекурами есть перерыв, покурил внеочереди - нехороший человек, но осуждать тебя некому, кроме себя самого. Итак, прежде чем начать, давай настроим время между перекурами (в минутах):");
		$user_data["action"] = "pause_set";
	} elseif ($user_data["action"] == "pause_set") {
		if ((int)$text == 0) {
			$bot->sendMessage($chat_id, "Что-то пошло не так. Введите только цифры, без букв и пробелов");
		} else {
			$pause = (int)$text;
			$bot->sendMessage($chat_id, "Время перерыва: $pause мин. Помни одно простое правило: покурил - нажми кнопку, иначе ничего не получится, удачи!", big_blue_button());
			$user_data["pause_time"] = $pause;
			$user_data["action"] = "on_game";
		}
	} elseif ($user_data["action"] == "on_game") {
		if ($text == "Покурил") {
			$last_smoke = count($user_data["stat"]) - 1;
			if ($last_smoke == -1) {
				$user_data["stat"][] = time();
				$bot->sendMessage($chat_id, 
					"Это Ваш первый сеанс. Следующий через " . $user_data["pause_time"] . "мин.");
			} else {
				$diff = time() - $user_data["stat"][$last_smoke];
				$user_data["stat"][] = time();

				if ($diff >= $user_data["pause_time"]*60) {
					$message = "Так держать!";
				} else {
					$message = "Сорвался. Соберись! У тебя все получится!";
				}

				$bot->sendMessage($chat_id, 
					$message . "\n\nСледующий сеанс через " . $user_data["pause_time"] . "мин.\n" .
					"Количество сеансов: " . count($user_data["stat"]));
			}
		}
	}

	update_userdata($user_data);

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

	function check_user($id, $chat_id) {
		$file = file_get_contents("user_data.json");
		$user_data = json_decode($file, true);

		$user_cell = -1;
		foreach ($user_data as $i => $user) {
			if (isset($user["id"]) && $user["id"] == $id) {
				$user_cell = $i;
				break;
			}
		}

		if ($user_cell == -1) {
			$new_user = array(
				"id" => $id,
				"chat" => $chat_id,
				"pause_time" => 0,
				"message_sent" => false,
				"action" => "on_start",
				"stat" => array()
			);
			update_userdata($new_user);
			return $new_user;
		} else {

			return $user_data[$user_cell];
		}
	}

	function update_userdata($data) {
		$file = file_get_contents("user_data.json");
		$user_data = json_decode($file, true);

		$user_cell = -1;
		foreach ($user_data as $i => $user) {
			if ($user["id"] == $data["id"]) {
				$user_cell = $i;
				break;
			}
		}

		if ($user_cell == -1) {
			$user_data[] = $data;
		} else {
			$user_data[$user_cell] = $data;
		}

		file_put_contents("user_data.json", json_encode($user_data), LOCK_EX);
	}
?>
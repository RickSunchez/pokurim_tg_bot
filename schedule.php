<?php
	include "/var/www/u1022297/data/www/shinesquad.ru/tg_bots/pokurim_tg_bot/TG_API.php";

	$user_data_file = "/var/www/u1022297/data/www/shinesquad.ru/tg_bots/pokurim_tg_bot/user_data.json";

	$file = file_get_contents("/var/www/u1022297/data/www/shinesquad.ru/tg_bots/pokurim_tg_bot/secret.json");
	$secret = json_decode($file, true);
	$bot = new TelegramBot($secret["api_key"]);

	$file = file_get_contents($user_data_file);
	$user_data = json_decode($file, true);

	foreach ($user_data as $i => $user) {
		if (count($user["stat"]) > 0) {
			$diff = time() - $user["stat"][count($user["stat"])-1];
			if ($diff >= $user["pause_time"]*60 && !$user["message_sent"]) {
				$bot->sendMessage($user["chat"], "У вас получается! Так держать!");
				$user_data[$i]["message_sent"] = true;
			}
		}
	}

	file_put_contents($user_data_file, json_encode($user_data), LOCK_EX);
?>

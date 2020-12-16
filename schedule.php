<?php
	include "TG_API.php";
	include "php/db_link.php";

	$file = file_get_contents("secret.json");
	$secret = json_decode($file, true);
	$bot = new TelegramBot($secret["api_key"]);

	$sql = "SELECT `stat_table`, `pause_time`, `chat`, `onMessage` FROM `users`";
	$res = mysqli_query($link, $sql);

	while ($row = mysqli_fetch_assoc($res)) {
		if ($row["onMessage"]) continue;

		$table = $row["stat_table"];
		$pause = $row["pause_time"];
		$chat  = $row["chat"];

		$sql = "SELECT 
					`_timestamp_` 
				FROM (
						SELECT 
							`_timestamp_` 
						FROM `$table` 
						ORDER BY `_timestamp_` 
						DESC LIMIT 1
					) as `lastts`
				WHERE 
					".time()." - `lastts`.`_timestamp_` > $pause";

		$sub = mysqli_query($link, $sql);
		if ($sub->num_rows != 0) {
			$bot->sendMessage($chat, "У вас получается! Так держать!");
			echo 1;
			$sql = "UPDATE `users` 
					SET `onMessage`=1
					WHERE `chat`=" . $chat;
			mysqli_query($link, $sql);
		}
	}
// /var/www/u1022297/data/www/shinesquad.ru/tg_bots/pokurim_tg_bot/
?>

<?php
	Class User {
		private $chat_id;
		private $db_link;
		public $action;
		public $pause;

		function __construct(int $chat_id, object $db_link) {
			$this->chat_id = $chat_id;
			$this->db_link = $db_link;

			$this->check_user();
		}

		function set_pause($delay) {
			$sql = "UPDATE `users` 
					SET `pause_time`=$delay
					WHERE `chat`=".$this->chat_id;
			mysqli_query($this->db_link, $sql);

			$this->pause = $delay;
		}

		function set_action($action) {
			$sql = "UPDATE `users` 
					SET `action`='$action'
					WHERE `chat`=".$this->chat_id;
			mysqli_query($this->db_link, $sql);
		}

		function add_event($evt=null) {
			$table = "u" . $this->chat_id;
			$time  = ($evt == null)
				? time()
				: $evt;

			$sql = "INSERT INTO `$table`(`_timestamp_`)
					VALUES (
						$time
					)";
			mysqli_query($this->db_link, $sql);
		}

		function get_last() {
			$table = "u" . $this->chat_id;
			$sql = "SELECT `_timestamp_` FROM `$table` ORDER BY `_timestamp_` DESC LIMIT 1";

			$res = mysqli_query($this->db_link, $sql);
			if ($res->num_rows == 0) {
				return false;
			} else {
				$data = mysqli_fetch_assoc($res);
				return $data["_timestamp_"];
			}
		}

		function set_msg($state) {
			$sql = "UPDATE `users` 
					SET `onMessage`=$state
					WHERE `chat`=".$this->chat_id;
			mysqli_query($this->db_link, $sql);
		}

		function get_count() {
			$table = "u" . $this->chat_id;
			$sql = "SELECT * FROM `$table`";
			$res = mysqli_query($this->db_link, $sql);
			return $res->num_rows;
		}

		private function check_user() {
			$sql = "SELECT * FROM `users` WHERE `chat`=".$this->chat_id;
			$res = mysqli_query($this->db_link, $sql);

			if ($res->num_rows == 0) {
				$this->new_user();
			} else {
				$data = mysqli_fetch_assoc($res);
				$this->action = $data["action"];
				$this->pause  = $data["pause_time"];
			}
		}
		private function new_user() {
			$table = "u" . $this->chat_id;
			$sql = "INSERT INTO 
						`users`(`id`, `chat`, `pause_time`, `onMessage`, `action`, `stat_table`) 
					VALUES (
						NULL,
						".$this->chat_id.",
						0,
						0,
						'on_start',
						'$table'
					)";
			mysqli_query($this->db_link, $sql);

			$sql = "CREATE TABLE $table (
				_timestamp_ INT
			)";
			mysqli_query($this->db_link, $sql);

			$this->action = "on_start";
			$this->pause = 0;
		}
	}
?>
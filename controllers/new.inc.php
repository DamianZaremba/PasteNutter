<?php
if(isset($_POST) && array_key_exists("content", $_POST) && array_key_exists("recaptcha_challenge_field", $_POST) && array_key_exists("recaptcha_response_field", $_POST)) {
	if (isset($_SERVER["REMOTE_ADDR"])) {
		$user = $_SERVER["REMOTE_ADDR"];
	} else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$user = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
		$user = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		$user = "Unknown";
	}

	 $resp = recaptcha_check_answer($recaptcha_privkey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	if(!$resp->is_valid) {
		$smarty->assign('content', $_POST['content']);
		$smarty->assign('syntax', $_POST['syntax']);
		$smarty->assign('recaptcha_box', recaptcha_get_html($recaptcha_pubkey));
		$smarty->assign('badCaptcha', True);
		$smarty->display('new.tpl');
		die();
	}

	// Only get users active in the past 10min - we use this in case the bot dies
	$tlimit = (String) time()-(60*10);
	$result = mysql_query("SELECT * FROM `irc_users` WHERE `host` = '" . mysql_real_escape_string($user) . "' AND `ping` > '" . $tlimit . "' LIMIT 0,1");
	$ircnick = False;
	if(mysql_num_rows($result) === 1) {
		$data = mysql_fetch_assoc($result);
		$ircnick = $data['nick'];
	}

	$query = "INSERT INTO `pastes` (`id`, `user`, `syntax`, `paste`, `views`, `downloads`)VALUES (NULL, ";
	if($ircnick === False) {
		$query .= "'" . mysql_real_escape_string($user) . "', ";
	} else {
		$query .= "'" . mysql_real_escape_string($ircnick) . "', ";
	}

	if(isset($_POST) && array_key_exists("syntax", $_POST) && !empty($_POST["syntax"])) {
		$syntax = $_POST['syntax'];
		$query .= "'" . mysql_real_escape_string($syntax) . "', ";
	} else {
		$syntax = False;
		$query .= "NULL, ";
	}
	$query .= "'" . mysql_real_escape_string($_POST['content']) . "',  0,  0)";
	mysql_query($query);

	$id = mysql_insert_id();
	if(($ircnick === False && $rc_unknown === True) || $ircnick !== False) {
		$socket = @fsockopen("udp://" . $rc_host . ":" . $rc_port);
		if($socket) {
			$data = json_encode(array(
				"user" => ($ircnick === False) ? $user : $ircnick,
				"url" => $base_url . "/" . $id,
				"format" => $syntax,
			));
			fwrite($socket, $data);
			fclose($socket);
		}
	}
	header('Location: ' . $base_url . '/' . $id);
} else {
	$smarty->assign('recaptcha_box', recaptcha_get_html($recaptcha_pubkey));
	$smarty->display('new.tpl');
}
?>

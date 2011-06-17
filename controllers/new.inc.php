<?php
/*
  * This file is part of PasteNutter.
  *
  * PasteNutter is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.
  *
  * PasteNutter is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with PasteNutter.  If not, see <http://www.gnu.org/licenses/>. 
  */

if(isset($_POST) && array_key_exists("content", $_POST)) {
	if (isset($_SERVER["REMOTE_ADDR"])) {
		$user = $_SERVER["REMOTE_ADDR"];
	} else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$user = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
		$user = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		$user = "Unknown";
	}

	if($recaptcha_enabled === True) {
		if(array_key_exists("recaptcha_challenge_field", $_POST) && array_key_exists("recaptcha_response_field", $_POST)) {
			$resp = recaptcha_check_answer($recaptcha_privkey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			if(!$resp->is_valid) {
				$smarty->assign('content', $_POST['content']);
				$smarty->assign('syntax', $_POST['syntax']);
				$smarty->assign('recaptcha_box', recaptcha_get_html($recaptcha_pubkey));
				$smarty->assign('badCaptcha', True);
				die();
			}
		} else {
				$smarty->assign('content', $_POST['content']);
				$smarty->assign('syntax', $_POST['syntax']);
				$smarty->assign('recaptcha_box', recaptcha_get_html($recaptcha_pubkey));
				$smarty->assign('badCaptcha', True);
				$smarty->display('new.tpl');
				die();
		}
	}

	// Only get users active in the past 10min - we use this in case the bot dies
	$tlimit = (String) time()-(60*10);
	$result = mysql_query("SELECT * FROM `irc_users` WHERE `host` = '" . mysql_real_escape_string($user) . "' AND `ping` > '" . $tlimit . "'");
	$ircnick = False;
	if(mysql_num_rows($result) > 0) {
		$ircnick = '';
		while($data = mysql_fetch_assoc($result)) {
			if($ircnick != '')
				$ircnick .= '/';
			$ircnick .= $data['nick'];
		}
	}

	$query = "INSERT INTO `pastes` (`id`, `time`, `user`, `ip_address`, `syntax`, `paste`, `views`, `downloads`)VALUES (NULL, ";
	$query .= "'" . time() . "', ";

	if($ircnick === False) {
		$query .= "'" . mysql_real_escape_string($user) . "', ";
	} else {
		$query .= "'" . mysql_real_escape_string($ircnick) . "', ";
	}

	$query .= "'" . mysql_real_escape_string($user) . "', ";

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
	if($recaptcha_enabled === True) {
		$smarty->assign('recaptcha_box', recaptcha_get_html($recaptcha_pubkey));
	}
	$smarty->display('new.tpl');
}
?>

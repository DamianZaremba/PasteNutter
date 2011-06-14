<?php
if(!isset($pasteID)) {
	$smarty->assign('error', 'No paste ID was specified');
	$smarty->display('view_error.tpl');
	die();
}

$result = mysql_query("SELECT * FROM `pastes` WHERE `id` = '" . mysql_real_escape_string($pasteID) . "' LIMIT 0,1");
if(mysql_num_rows($result) === 0) {
	$smarty->assign('error', 'Invalid paste ID was specified');
	$smarty->display('view_error.tpl');
	die();
}
$data = mysql_fetch_assoc($result);

$download = False;
if(isset($_GET) && array_key_exists("download", $_GET)) {
	$download = True;
}

if($download === True) {
	mysql_query("UPDATE `pastes` SET `downloads` = `downloads`+1 WHERE `id` = '" . mysql_real_escape_string($data['id']) . "'");
	header('Content-disposition: attachment; filename=' . $pasteID . '.txt');
	header('Content-type: text/plain');
	print $data['paste'];
} else {
	mysql_query("UPDATE `pastes` SET `views` = `views`+1 WHERE `id` = '" . mysql_real_escape_string($data['id']) . "'");
	$smarty->assign('author', $data['user']);

	if(!$data['syntax']) {
		$paste = "<pre>" . htmlentities($data['paste']) . "</pre>";
	} else {
		$geshi = new GeSHi($data['paste'], $data['syntax'], $base_dir . '/lib/geshi/');
		if($geshi) {
			$paste = $geshi->parse_code();
		} else {
			$paste = "<pre>" . htmlentities($data['paste']) . "</pre>";
		}	
	}

	$smarty->assign('content', $paste);
	$smarty->assign('id', $data['id']);
	$smarty->display('view.tpl');
}
?>

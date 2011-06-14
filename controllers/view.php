<?php
if(isset($_GET) && array_key_exists("id", $_GET)) {
	$id = $_GET['id'];
} else {
	$smarty->assign('error', 'No paste ID was specified');
	$smarty->display('view_error.tpl');
	die();
}

$result = mysql_query("SELECT * FROM `pastes` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 0,1");
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
	header('Content-disposition: attachment; filename=' . $id . '.txt');
	header('Content-type: text/plain');
	print 'test paste';
} else {
	mysql_query("UPDATE `pastes` SET `views` = `views`+1 WHERE `id` = '" . mysql_real_escape_string($data['id']) . "'");
	$smarty->assign('author', $data['user']);

	if(!$data['syntax']) {
		$paste = $data['paste'];
	} else {
		$geshi = new GeSHi($data['paste'], $data['syntax'], $base_dir . '/lib/geshi/')
		if($geshi) {
			$paste = $geshi->parse_code();
		} else {
			$paste = $data['paste'];
		}	
	}
	$smarty->assign('content', $paste);
	$smarty->display('view.tpl');
}
?>

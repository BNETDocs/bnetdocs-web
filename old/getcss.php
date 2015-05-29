<?
	$userid = $_GET['user'];
	
	$usercss = GetData($userid, 'usercss');

	echo $usercss;
?>
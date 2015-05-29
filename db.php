<?
	######################
	# Database Key Script
	######################

	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('There was an error.'); # Keep error obscure, so hackers can't figure out what went wrong.
	
	# Begin Code
	# -------------

	$dbhost = 'localhost';
	$dbusername = 'bnetdocs';
	$dbpasswd = 'redux123';
	$database_name = 'bnetdocs_botdev';

	@$connection = mysql_connect("$dbhost","$dbusername","$dbpasswd") or die ("Couldn't connect to server because ".mysql_error());
	@$db = mysql_select_db("$database_name", $connection) or die("Couldn't select database because ".mysql_error());
?>
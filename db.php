<?
	######################
	# Database Key Script
	######################

	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------

	$dbhost = $config->database->hostname;
	$dbusername = $config->database->username;
	$dbpasswd = $config->database->password;
	$database_name = $config->database->name;

        global $sql_connection;
	@$sql_connection = mysqli_connect("$dbhost","$dbusername","$dbpasswd","$database_name") or die ("Couldn't connect to server because ".mysqli_error());
	@mysqli_query('SET NAMES ' . $config->database->character_set . ' COLLATE ' . $config->database->collation . ';');
?>

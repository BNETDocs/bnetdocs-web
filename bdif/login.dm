<?
	global $auth, $ip;
	
	if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	$rememberme = $_POST['rememberme'];
?>
		<div id="console">
		<form method="post" onsubmit="return validate_form(this)" action="/?op=login"><br>
		&nbsp;&nbsp;Username:<br>
		&nbsp;&nbsp;<input type="text" id="inputbox" name="username"><br><br>
		&nbsp;&nbsp;Password:<br>
		&nbsp;&nbsp;<input type="password" id="inputbox" name="password"><br><br>
		&nbsp;&nbsp;<input type="checkbox" id="inputbox" name="rememberme" value="yes">Remember me<br><br>
		&nbsp;&nbsp;<input type="submit" id="abutton" value="Login"><br>
		</form>
		<br><center><a href="/?op=register">Register</a>&nbsp;|&nbsp;<a href="/?op=resetpw">Password Reset</a></center>
		<br>
		</div>

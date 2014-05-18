<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ip;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------

	if($_POST['email']) $_POST['email'] = trim($_POST['email']);
	
	if(!$_POST['email']){
		?>
			<div id="container">
			<div id="main2">
			<h2>Reset Password</h2>
			<blockquote>
			<form method="post" action="/?op=resetpw"><br>
			&nbsp;&nbsp;Your Email:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="email"><br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Reset Password"><br>
			</form>
			<br></blockquote></div></div>
		<?
		$error = 'true';
	} else {
		if($_POST['email']){
			if(FieldVerify("email", "users", $_POST['email'])){
				$userid = getinfo('users', 'email', $_POST['email'], 'id');
				ResetPassword($userid);
				?><div id="container">
				<div id="main2">
				<h2>Reset Password</h2>
				<br><br>
				<center><b>Your password has been reset and emailed to you.</b></center>
				<br><br><br>
				</div></div><?
			} else {
				?>
				<div id="container">
				<div id="main2">
				<h2>Reset Password</h2>
				<blockquote>
				<form method="post" action="/?op=resetpw"><br>
				&nbsp;&nbsp;Your Email:<br>
				&nbsp;&nbsp;<input type="text" id="inputbox" name="email">&nbsp;&nbsp;&nbsp;<b><font color=red>Invalid email (<? echo $_POST['email']; ?>).</font></b><br><br>
				&nbsp;&nbsp;<input type="submit" id="abutton" value="Reset Password"><br>
				</form>
				<br></blockquote></div></div>
				<?
				$error = 'true';
			}
		}
	}
?>

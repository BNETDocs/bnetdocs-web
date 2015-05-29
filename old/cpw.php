<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ip;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	if(!$userid) die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------

	if($_POST['oldpw']) $_POST['oldpw'] = trim($_POST['oldpw']);
	if($_POST['newpw']) $_POST['newpw'] = trim($_POST['newpw']);
	
	if(!$_POST['oldpw'] && !$_POST['newpw']){
		?>
			<div id="container">
			<div id="main2">
			<h2>Change Password</h2>
			<blockquote>
			<form method="post" action="/?op=cpw"><br>
			&nbsp;&nbsp;Old Password:<br>
			&nbsp;&nbsp;<input type="password" id="inputbox" name="oldpw"><br><br>
			&nbsp;&nbsp;New Password:<br>
			&nbsp;&nbsp;<input type="password" id="inputbox" name="newpw"><br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Change"><br>
			</form>
			<br></blockquote></div></div>
		<?
		$error = 'true';
	} else {
		if(!$_POST['oldpw']){
			?>
			<div id="container">
			<div id="main2">
			<h2>Change Password</h2>
			<blockquote>
			<form method="post" action="/?op=cpw"><br>
			&nbsp;&nbsp;Old Password:<br>
			&nbsp;&nbsp;<input type="password" id="inputbox" name="oldpw">&nbsp;&nbsp;&nbsp;<font color=red><b>REQUIRED!</b></font><br><br>
			&nbsp;&nbsp;New Password:<br>
			&nbsp;&nbsp;<input type="password" id="inputbox" name="newpw"><br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Change"><br>
			</form>
			<br></blockquote></div></div>
			<?
			$error = 'true';
		}
		if(!$_POST['newpw']){
			?>
			<div id="container">
			<div id="main2">
			<h2>Change Password</h2>
			<blockquote>
			<form method="post" action="/?op=cpw"><br>
			&nbsp;&nbsp;Old Password:<br>
			&nbsp;&nbsp;<input type="password" id="inputbox" name="oldpw"><br><br>
			&nbsp;&nbsp;New Password:<br>
			&nbsp;&nbsp;<input type="password" id="inputbox" name="newpw">&nbsp;&nbsp;&nbsp;<font color=red><b>REQUIRED!</b></font><br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Change"><br>
			</form>
			<br></blockquote></div></div>
			<?
			$error = 'true';
		}
		if($_POST['oldpw'] && $_POST['newpw']){
			$currentpassword = GetData($userid, 'password');
			$oldpassword = md5($_POST['oldpw']);
			$newpassword = md5($_POST['newpw']);
			
			if($oldpassword != $currentpassword){
				?>
				<div id="container">
				<div id="main2">
				<h2>Change Password</h2>
				<blockquote>
				<form method="post" action="/?op=cpw"><br>
				&nbsp;&nbsp;Old Password:<br>
				&nbsp;&nbsp;<input type="password" id="inputbox" name="oldpw">&nbsp;&nbsp;&nbsp;<font color=red><b>INVALID PASSWORD!</b></font><br><br>
				&nbsp;&nbsp;New Password:<br>
				&nbsp;&nbsp;<input type="password" id="inputbox" name="newpw"><br><br>
				&nbsp;&nbsp;<input type="submit" id="abutton" value="Change"><br>
				</form>
				<br></blockquote></div></div>
				<?
				$error = 'true';
			}
			if(!$error){
				# Now we can finally change the damn password.
				WriteData($userid, 'password', $newpassword);
				?><div id="container">
				<div id="main2">
				<h2>Change Password</h2>
				<br><br>
				<center><b>Your password has been changed successfully.</b></center>
				<br><br><br>
				</div></div><?
			}
		}
	}
?>

<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ip;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------

	if(!$_POST['email'] && !$_POST['username']){
		?>
			<div id="container">
			<div id="main2">
			<h2>Membership Registration</h2>
			<blockquote>
			<form method="post" onsubmit="return validate_form(this)" action="/?op=register"><br>
			&nbsp;&nbsp;Username:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="username"><br><br>
			&nbsp;&nbsp;Email:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="email"><br><br>
			&nbsp;&nbsp;<input type="checkbox" id="inputbox" name="agree" value="yes">I have read and agree to the <a href="/?op=legalism">terms of service</a>.<br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Register"><br>
			</form>
			<br></blockquote></div></div>
		<?
		$error = 'true';
	} else {
		if(!$_POST['agree']){
			$agree = "<br><br><center><b><font color=red>YOU NEED TO AGREE TO THE TOS BEFORE REGISTERING!</font></b></center>";
			$error = 'true';
		}
		if(!$_POST['email']){
			?><div id="container">
			<div id="main2">
			<h2>Membership Registration</h2>
			<blockquote>
			<form method="post" onsubmit="return validate_form(this)" action="/?op=register"><br>
			&nbsp;&nbsp;Username:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="username" value="<?=$_POST['username'];?>"><br><br>
			&nbsp;&nbsp;Email:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="email"> <font color=red><b>MISSING EMAIL!</b></font><br><br>
			&nbsp;&nbsp;<input type="checkbox" id="inputbox" name="agree" value="yes">I have read and agree to the <a href="/?op=legalism">terms of service</a>. <?=$agree;?><br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Register"><br>
			</form>
			<br></blockquote></div></div><?
			$error = 'true';
		}
		if(!$_POST['username']){
			?><div id="container">
			<div id="main2">
			<h2>Membership Registration</h2>
			<blockquote>
			<form method="post" onsubmit="return validate_form(this)" action="/?op=register"><br>
			&nbsp;&nbsp;Username:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="username"> <font color=red><b>MISSING USERNAME!</b></font><br><br>
			&nbsp;&nbsp;Email:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="email" value="<?=$_POST['email'];?>"><br><br>
			&nbsp;&nbsp;<input type="checkbox" id="inputbox" name="agree" value="yes">I have read and agree to the <a href="/?op=legalism">terms of service</a>. <?=$agree;?><br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Register"><br>
			</form>
			<br></blockquote></div></div><?
			$error = 'true';
		}
		if($_POST['email'] && $_POST['username'] && !$_POST['agree']){
			?><div id="container">
			<div id="main2">
			<h2>Membership Registration</h2>
			<blockquote>
			<form method="post" onsubmit="return validate_form(this)" action="/?op=register"><br>
			&nbsp;&nbsp;Username:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="username" value="<?=$_POST['username'];?>"><br><br>
			&nbsp;&nbsp;Email:<br>
			&nbsp;&nbsp;<input type="text" id="inputbox" name="email" value="<?=$_POST['email'];?>"><br><br>
			&nbsp;&nbsp;<input type="checkbox" id="inputbox" name="agree" value="yes">I have read and agree to the <a href="/?op=legalism">terms of service</a>. <br><br>
			&nbsp;&nbsp;<input type="submit" id="abutton" value="Register"><br>
			</form>
			<br></blockquote></div></div><?
			$error = 'true';
		}
		if($_POST['email'] && $_POST['username'] && $_POST['agree']){
			if(FieldVerify('username', 'users', $_POST['username'])){
				?><div id="container">
				<div id="main2">
				<h2>Membership Registration</h2>
				<blockquote>
				<form method="post" onsubmit="return validate_form(this)" action="/?op=register"><br>
				&nbsp;&nbsp;Username:<br>
				&nbsp;&nbsp;<input type="text" id="inputbox" name="username"> <font color=red><b>USERNAME ALREADY TAKEN!</b></font><br><br>
				&nbsp;&nbsp;Email:<br>
				&nbsp;&nbsp;<input type="text" id="inputbox" name="email" value="<?=$_POST['email'];?>"><br><br>
				&nbsp;&nbsp;<input type="checkbox" id="inputbox" name="agree" value="yes">I have read and agree to the <a href="/?op=legalism">terms of service</a>. <br><br>
				&nbsp;&nbsp;<input type="submit" id="abutton" value="Register"><br>
				</form>
				<br></blockquote></div></div><?
				$error = 'true';
			}
			if(FieldVerify('email', 'users', $_POST['email'])){
				$alias = GetInfo('users', 'email', $_POST['email'], 'username');
				?><div id="container">
				<div id="main2">
				<h2>Membership Registration</h2>
				<center><b><font color=red>
				You're already registered under the username of <?=$alias;?>.
				</font></b></center>
				</div></div><?
				$error = 'true';
			}
			if(!$error){
				# Now we can finally create the damn account.
				$username = $_POST['username'];
				$email = $_POST['email'];
				CreateUser($username, $email, $ip);
				?><div id="container">
				<div id="main2">
				<h2>Membership Registration</h2>
				<blockquote><b>You've been registered. Please check your email for your password. If you have not logged in 
				for two weeks from the time of registration, your account will be automatically deleted. Once 
				you login, your account will be secured.</b></blockquote>
				</div></div><?
			}
		}
	}
?>

<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ip;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	$username == trim($_POST['username']);
	$password == trim($_POST['password']);
	
	if(trim($username) == ""|| trim($password) == ""){
		error('Missing username or password.');
	} else {
		$rememberme = $_POST['rememberme'];
		$time = time();
		$loginresults = login($username,$password, "yes", $ip);
		if ($loginresults == "pass"){
			$userid = GetUserIDFromSession($ip);
			if(CheckBanned($ip, 'total')){
				logthis($userid, 'Attempted to enter the site, attempt blocked. '.$ip.' is ipbanned.', 'ipban');
				die('We\'re experiencing problems. Please check back later. Thank you.');
			}
			if(FieldVerify('id', 'banned', $userid)){
				#Ban evasion! Add new ip to list!
				logthis($userid, 'IPBan evasion detected. Attempted to enter the site, attempt blocked. New IP address of '.$ip.' is now ipbanned.', 'ipban');
				mysql_query("INSERT INTO `banned` (id, ip, bantype) VALUES ($userid, '$ip', 'total')") or die("Login Block Error: ".mysql_error());
				die('We\'re experiencing problems. Please check back later. Thank you.');
			}
			if($rememberme == 'yes'){
				mysql_query("UPDATE `sessions` SET notimelimit=1 WHERE `id`=$userid") or die("Session Error: ".mysql_error());		
			}
			#logthis($userid, 'logged in.', 'session');
			WriteData($userid, 'secured', 'true');
			redirect("/"); // Redirect correct member
		} else {
			error($loginresults);
		}
	}
?>
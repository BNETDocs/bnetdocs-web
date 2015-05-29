<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ip, $userid;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	if(!$userid){
		logthis($userid, 'Logout attempted without a local session. Attempt ignored.', 'hack');
		blockhack();
	}
	
	#log 'em out
	KillSession($userid);
	#logthis($userid, 'logged out.', 'session');
	redirect('/?msg=1');
?>
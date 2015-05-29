<?
	// Jailout2000: This file was added by me.
	# Task Manager

	# Block Direct Access Attempts
	# -------------------------------
	
		global $auth, $ie, $userid;
		if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		if(rank($userid) < 4) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
		ob_start();
		
		

	# End Code
	# -------------		
		$out = ob_get_clean();
		DisplayDialogBox('Forums' , $out);
?>
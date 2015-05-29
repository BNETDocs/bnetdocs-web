<?
	########################
	# Comment Post Script
	########################

	# Security
	# -------------------------------
		
		# Prevent remote access
		global $auth;
		if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		
		# Check to see if nothing was posted, usually indicative of spambots looking for a way in
		if(!$_POST) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		
		# Check to see if user is a member. 
		if(!$userid) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		
	# Begin Code
	# -------------
	
		# Collect, then make sure all of the needed data is there, otherwise, error out.
		
		$catid = $_POST['catid'];
		$subid = $_POST['subid'];
		$thecomment = $_POST['mycomment'];
		
		if(!$catid || !$subid || !$thecomment) die ('Unable to comply, missing data.');
		
		# Now to post it!
		
		$sqlquery2 = 'SELECT * FROM comments WHERE catid='.$catid.' AND subid='.$subid.' ORDER BY id ASC';
		mysql_query("INSERT INTO comments (posterid, catid, subid,message) VALUES ('$userid','$catid','$subid', '$thecomment')") or die("PostComment.php Comment->SQL Insert Error: ".mysql_error()." $userid".$userid);
		
		# Log it!
		if($catid = 1){ 
			$page = 'news';
			$iden = 'nid';
		} else if($catid = 2){ 
			$page = 'packet';
			$iden = 'id';
		}
		
		$title = GetInfo('news', 'id', $subid, 'subject');
		
		$event = 'Commented on <a href="/?op='.$page.'&'.$iden.'='.$subid.'#comments">'.$title.'</a> saying: '.$thecomment;
		logthis($userid, $event, 'commentadd');
		
		# All done, send user back to comments with message that post was successful!
		
		WriteData($userid, 'msg', '<font class=\"sysmsg\">Thanks for commenting!</font>');
		
		redirect('/?op='.$page.'&'.$iden.'='.$subid.'#comments');
?>
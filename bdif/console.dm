<? #Console.dm
	global $ip, $userid;
	
	if(!$userid){
		logthis($userid, 'Attempted to force direct access to console without logging in. Attempt blocked.', 'hack');
		blockhack();
	}
	
	$rank = rank($userid);
	if($rank > 2) echo '<a href="/?op=news&mode=post">Post News</a><br>';
	if($rank > 2) echo '<a href="/?op=viewlogs">View Logs</a><br>';
	if($rank > 2) echo '<a href="/?op=packet&mode=add">Add Packet</a><br>';
	if($rank > 2) echo '<a href="/?op=doc&mode=add">Add Document</a><br>';
	echo '<a href="/?op=csseditor">CSS Theme Editor</a><br>';
	echo '<a href="/?op=cpw">Change Password</a><br>';
	echo '<a href="/?op=logout">Log Out</a><br>';
?><br>
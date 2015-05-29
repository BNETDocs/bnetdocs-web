<?
	# Packet Display

	# Block Direct Access Attempts
	# -------------------------------
	
		global $auth, $ie, $userid;
		if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		if(rank($userid) < 4) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		ob_start();	# Start buffering
	
	# Begin Code
	# -------------
		
		# Gather info on packet
		
		$pid = $_REQUEST['id'];	# Get database id of packet to display
		$thedb = 'packets';
		$theindex = 'id';

		$messageid = GetInfo($thedb, $theindex, $pid, 'messageid');
		$messagename = GetInfo($thedb, $theindex, $pid, 'messagename');
		$direction = GetInfo($thedb, $theindex, $pid, 'direction');
		$protocol = GetInfo($thedb, $theindex, $pid, 'protocol');
		$usedby = GetInfo($thedb, $theindex, $pid, 'usedby2');
		$status = GetInfo($thedb, $theindex, $pid, 'status');
		$clearance = GetInfo($thedb, $theindex, $pid, 'clearance');
		
		$pagetitle = $messagename.' ('.$messageid.')';
		$pagetitle .= ' Packet Information';	# Set title of packet

		
		# Display page
		$out = ob_get_clean();
		if(!$pagetitle) $pagetitle = 'Missing Title';
		DisplayDialogBox($pagetitle, $out);
		$catid = 2;	#Packet category
		$subid = $pid;
		include 'comments.php';
?>
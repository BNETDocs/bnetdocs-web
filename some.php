<?
	# Set up environment
	#-------------

		$auth = "true";	# Authorization variable for other scripts, securiity against unauthorized script inclusion

	# Include essential files, self explainatory
	#-------------

		include_once('functions.php');	
		include_once('db.php');

		$userid = $_POST['userid'];
		
		$username == whoisid($userid);
		
		if($userid){
			echo "Javascript is enabled for ".$username;
		} else {
			echo "Javascript is not enabled for ".$username;
		}
?>
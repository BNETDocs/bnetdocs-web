<?
	# Declare security variable
	
	$auth = 'true';
	
	# Include needed files
	
	require_once 'db.php';
	require_once 'functions.php';
	
	# Security
	
	$code = $_GET['code'];
	
	if($code != "arta"){
		logthis($userid, 'Illegally attempted to access sessionhandler.php. Attempt blocked.', 'hack');
		blockhack();
	}
	
	# Pull up all sessions, get time, compare time last accessed site to current time.
	
	$sqlquery = 'SELECT * FROM sessions WHERE notimelimit=0 ORDER BY dtstamp ASC';
	$membersarray = mysql_query($sqlquery);
	while($row = mysql_fetch_array($membersarray)){
		$userid = $row['id'];
		$dtstamp = strtotime($row['dtstamp']);
		$currenttime = time();
		#echo "$currenttime - $dtstamp = ".($currenttime - $dtstamp)." which converts to ".(($currenttime - $dtstamp) / 60)." minutes.<br>";
		if($currenttime - $dtstamp > 60 * 15){ #15 minutes since last logged activity has elapsed
			#log 'em out
			KillSession($userid);
			#logthis($userid, 'logged out.', 'session');
			#echo whoisid($userid).' has been identified as inactive.<br>';
		}
	} 
	
	# Pull up all unsecured accounts, get time, compare time of registration to current time.
	
	$sqlquery = 'SELECT * FROM users WHERE secured="" ORDER BY dtstamp ASC';
	$membersarray = mysql_query($sqlquery);
	while($row = mysql_fetch_array($membersarray)){
		$userid = $row['id'];
		$dtstamp = strtotime($row['dtstamp']);
		$currenttime = time();
		#echo "$currenttime - $dtstamp = ".($currenttime - $dtstamp)." which converts to ".(($currenttime - $dtstamp) / 60)." minutes.<br>";
		if($currenttime - $dtstamp > 60 * 60 * 24 * 7 * 2){ #2 weeks (seconds, minutes, hours, days, weeks)
			#kill 'em
			$body = "Hello!
		
You previously registered as an user of BNETDocs. If this email is in error, simply ignore this email.

If this is you, please read on. 
Username: $username

Unfortunately, because you did not confirm the registration for the last two weeks, your account has been deleted. All new accounts are required to confirm their registration.

Confirming registration is as simple as logging in your account at BNETDocs.

We regret having to remove the account. This was done to save space on the server. 

If you'd like your account back, please DO feel free to re-register, and this time, please remember to confirm your registration by logging in as soon as you get the registration confirmation email that contains your login information.

Website: http://www.bnetdocs.org

Once more, our apologies!

Regards,
BNETDocs

---------
Please do not respond to this email, this email was automatically generated.
If you have any questions, comments, etc, feel free to contact one of the administrators.
---------";
			$email = GetData($userid, 'email');
			Email($email, 'postmaster@bnetdocs.org', 'BNETDocs: A problem with your account!', $body);
			#logthis($userid, 'Failed to confirm their registration. Account deleted.', 'account');
			DeleteUser($userid);
		}
	} 
	# Finished checking accounts. 
	
	#Terminate.
	echo "handle session done.";
	exit();
?>

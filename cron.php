<?
	# Cron PHP
	#----------------------
	
	$code = $_REQUEST['code'];	# Get security code (if given)
	if($code != 'abc123'){	# Check to see if match. This is to ensure that this script is only executed locally. Not very secure, but it works well enough.
		#Ignore
		exit();
	} 
	
	# Now process Cron stuff
	
	$auth = 'true';	# Authorize use of includes
	include 'db.php';
	include 'functions.php';
	
	# Run stuff meant to be executed every 15 min here
	
	# Delete all sessions that have a time limit, and have a timestamp older than 15 minutes
	echo 'Processing sessions...    ';
	$SQL = 'DELETE FROM `sessions` WHERE dtstamp + INTERVAL 15 MINUTE <= NOW( ) AND notimelimit = 0';
	mysql_query($SQL) or die("Expired Session Deletion Error: ".mysql_error());
	echo 'Done! <br />';
	
	# Pull up all unsecured accounts and identify those who failed to confirm registration for two weeks and delete them to save space.
	echo 'Processing expired unsecured registrations...    ';
	$SQL = 'SELECT * FROM users WHERE secured="" AND dtstamp + INTERVAL 2 WEEK <= NOW()';
	
	$membersarray = mysql_query($SQL)  or die("Expired Unsecured Member Selection Error: ".mysql_error());
	while($row = mysql_fetch_array($membersarray)){
		$userid = $row['id'];
		$username = $row['username'];
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
		logthis($userid, 'Failed to confirm their registration. Account deleted.', 'account');
		DeleteUser($userid);
	} 
	echo 'Done!<br />';

	# Finished
	exit();		
?>
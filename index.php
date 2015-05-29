<?
	# Set up environment
	#-------------

		$auth = "true";	# Authorization variable for other scripts, securiity against unauthorized script inclusion
		$ip = $_SERVER['REMOTE_ADDR'];	# Save user's ip address to variable
		$surpress = $_REQUEST['surpress'];	# Check to see if header surpression is needed, rare
		$op = $_REQUEST['op'];	# Get name of file requested
		$lastloc = $op;
		if($lastloc == 'logout') $lastloc = 'news';

	# Include essential files, self explainatory
	#-------------
		
		require('functions.php');	# Obvious
		require('db.php');			# Initializes database interface
		require ('cbparser.php');	# BBCode parser, obtained from corz.org to save on coding.
		
		# Check to see if site is under maintenance, if so, die with a message informing user of it
		if(GetSettingValue('maintenance') == 'true') die('Site is under maintenance, please check back later. We apologize for any problems this may cause.');
		
	# Security
	#-------------

		# Stripslashes as shown on http://php.net/get_magic_quotes_gpc
		# Used to prevent remote code injection via post, get, cookie. Also 
		# contributes to prevent remote code injection via SQL since input 
		# is cleaned prior to inserting to SQL DB.
		
		if (get_magic_quotes_gpc()) {	
		   function stripslashes_deep($value) {
		       $value = is_array($value) ?
		                   array_map('stripslashes_deep', $value) :
		                   stripslashes($value);

		       return $value;
		   }

		   $_POST = array_map('stripslashes_deep', $_POST);			# Clean POSTed data
		   $_GET = array_map('stripslashes_deep', $_GET);					# Clean browser variables
		   $_COOKIE = array_map('stripslashes_deep', $_COOKIE);	# Clean cookie variables
		}
		
	# Session Handler
	#--------------------------
	
		# Check to see if a prior session exists, if so, resume session
		
		# Check against IP-Based sessions
		$userid = GetUserIDFromSession($ip);
		if(!$userid){ 	# No ip-based session found, try checking for a cookie
			if (isset($_COOKIE['identityhash'])) {
				$identityhash = $_COOKIE['identityhash'];		# Pull identity hash value from the cookie
				if(FindIdentityHash($identityhash)){	# Check database for identity hash
					$userid = GetIDFromIdentityHash($identityhash);
					if(TimeLimited($userid)){	# Find out if user's session had a time limit on it
						KillCookie();		# Has a time limit, so kill the cookie
					}
					ResumeSession($userid, false, $ip);	# Obvious
				}
			}
		}  else {
			# IP-based session found
			if(TimeLimited($userid) == true) {
				if (isset($_COOKIE['identityhash'])) {	# Check to see if cookie exists
					KillCookie();	# Has a time limit, so kill the cookie
				}
				ResumeSession($userid, false, $ip);		# Obvious
			}  else {
				ResumeSession($userid, true, $ip);	# Obvious
			}
		}
		
		# Is the user trying to log out?
		if($op == 'logout') {
			KillSession($userid);	# Destroy session
			$op = ''; # Redirect the user to the default page
			$msg = '1'; # Inform user of successful logout
			$userid = false;
		}
		
	# Block out unwelcome visitors
	#----------------------------------------------
	
		if(CheckBanned($ip, 'total')){	# Compare visitor's IP address to the Banned database (a blacklist basically)
			logthis($userid, 'User is ipbanned. View request denied.', 'ipban');
			die('We\'re experiencing unwanted problems. Please check back later. Thank you.');		# Heh, heh... Unwanted problems... ;-)
		}
		
		# Sometimes an user will attempt to login from a different ip address. If that happens, ban that IP address 
		# as well so they cannot attempt to create an account. If the user has a dynamic IP address, the ip-ban would 
		# normally be defeated. However, if the user attempts to login a banned account via a different IP, the attempt
		# is detected as ban evasion and the new IP address is added to the IP-Ban Blacklist. But if an user is unusually 
		# smarter than the norm kiddie, they will not attempt to login their old account, and will instead create a new 
		# account as to evade the ban. One solution to this is to leave a cookie when the user first is banned, so if they 
		# attempt this tactic, they would be caught. However if the user deletes cookies prior to accessing the site, or 
		# use a different computer, while having a different IP address, and doesn't login the banned account (and instead 
		# creates a new one) -- the cookie route would then be defeated. There is no surefire way to keep a banned visitor out, 
		# so simply tracking the banned user via their user id, ip address, and cookie will suffice. Eventually the user will 
		# get annoyed at being banned and go away or maybe even behave.
		
		if(FieldVerify('dbid', 'banned', $userid)){	# Check to see if user id exists in banned table
			#Ban evasion! Add new ip to list!
			logthis($userid, 'Ban evasion detected. New IP address ipbanned.', 'ipban');
			mysql_query("INSERT INTO `banned` (dbid, ip, bantype) VALUES ($userid, '$ip', 'total')") or die("Index Block Error: ".mysql_error());
			die('We\'re experiencing unwanted problems. Please check back later. Thank you.');   # Heh, heh... Unwanted problems... ;-)
		}
		
		if (isset($_COOKIE['abc123'])) {	# Check to see if cookie indicating ban exists
			#Ban evasion! Add new ip to list!
			logthis($userid, 'Ban evasion detected. New IP address ipbanned.', 'ipban');
			mysql_query("INSERT INTO `banned` (dbid, ip, bantype) VALUES ($userid, '$ip', 'total')") or die("Index Block Error: ".mysql_error());
			die('We\'re experiencing unwanted problems. Please check back later. Thank you.');   # Heh, heh... Unwanted problems... ;-)
		}
		
	# Additional miscellaenous operations
	#--------------------------------------------------------
		
		# A way to track accesses by Blizzard.
		if(isblizzard($ip)){
			logthis(-1, 'Blizzard accessed website.', 'vip');
		}

	# Start spitting out HTML
	#-------------

		# Check for login
		
		if($_POST['operation'] == 'login'){
			if(VerifyLogin($_POST['username'], $_POST['password'])){	# Login validated
				$userid = GetIDFromUsername($_POST['username']);		# Get User ID
				if($_POST['rememberme'] == 'yes'){
					$notimelimit = true;
				} else {
					$notimelimit = false;
				}
				ResumeSession($userid, $notimelimit, $ip);		# Should be obvious.
				WriteData($userid, 'msg', '<font class=\"sysmsg\">Welcome back to BNETDocs, '.whoisid($userid).'! Login Successful.</font>');		# Message confirming login
				$op = $_POST['lastloc'];		# Return user to last location
			} else {																				# Login failure
				$msg == '2';		# Message regarding failed login
				$op = $_POST['lastloc'];		# Return user to last location
			}
		}
		
		# Prevent infinite loop bug, bug found by Jailout2000
		if($op == 'index') $op = '';
		
		# Prevent remote inclusion injection
		$legal_inc=getdir('.');	# Check to see if file exists in current directory

		$i=0;
		$allowfile='false';		# Start out assuming file request is unauthorized
		while($i <= count($legal_inc)){	# Start searching for file requested
			if($op.'.php'==$legal_inc[$i]){
				$allowfile='true';	# If file is found, change assumption to authorized
				break;					# Exit loop, no need to keep searching
			}
			$i++;
		}
		
		if(!$op || trim($op) == '') $allowfile='true';	# If no file was requested (usually because user accessed index, which is fine), authorize it.
		
		if($allowfile=='false') die();	# If file request isn't authorized, quit processing. No message is displayed, makes it harder for hackers to figure out what went wrong.
		
		# Done with anti-remote-inclusion-injection
		
		if($op == '') $op = 'news';		# If no file was requested, direct user to the news (Default)

		if($_POST){
			if($_POST['operation'] == 'savesettings'){	# Requires pre-processing prior to display
				include $op.'.php';			# Include the requested file
			}
		}
		
		include 'if/header.dm';			# Include header file
		
		if($userid){		# If user is logged in, then...
			# Update member last login
			mysql_query("UPDATE sessions SET dtstamp=NOW() WHERE id='$userid'") or die("UpdateSession Function Error: ".mysql_error());
			$msg = GetData($userid, 'msg');	# Check to see if there's any system messages (login confirmation, action confirmation, warnings, etc, etc)
		} else {
			if(!$msg) $msg = $_REQUEST['msg'];	# If user isn't logged in and  there's a message passed via browser variable of msg, usually is a message confirming log out.
			if($msg == '1'){
				$msg = '<font class="sysmsg">You have been logged out.</font>';
			} elseif($msg == '2'){
				$msg = '<font class="errortext">Invalid Login.</font>';
			}  else {
				$msg = '';	# To keep people from messing with the msg variable to generate fake system messages ;-P
			}
		}
		if($msg){		# If there's a message, then format message for display, erase message from user's message cache, then display message
			$msg = '<center>'.$msg.'</center>';
			WriteData($userid, 'msg', '');
			DisplayDialogBox('', $msg, false, '', false, false, 'fade');
		}
		
		$op=$op.'.php';	# Append php to file name for inclusion
		include $op;			# Include the requested file

		if(GetOption($userid, 'hidesidebar') == 0){
			include 'if/sidebar.dm';		# Include sidebar
		}
		
		include 'if/footer.dm';		# Include footer

		mysql_close();		# Close MySQL connection (clean up)
		exit();		# Self explainatory

	#-------------
	# End of main script.
	#-------------
?>

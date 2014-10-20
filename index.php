<?
	
	# Include direct script access authorization variable and other variables
	#-------------

		$auth = "true";
		$ip = $_SERVER['REMOTE_ADDR'];
		$surpress = $_REQUEST['surpress'];
		#die('This site has been frozen for maintenance.');
		
	# Set up environment
	#-------------

		header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
		if($_SERVER['SERVER_PORT']!=443 || $_SERVER['HTTP_HOST']!='bnetdocs.org') {
			http_response_code(301);
			header('Location: https://bnetdocs.org'.$_SERVER['REQUEST_URI']);
			exit;
		}

	# Security
	#-------------
	
		# Prevent spam POST submissions from external domains
		
		if($_POST){
			if(!strpos($_SERVER['HTTP_REFERER'],'bnetdocs.org')){
				exit();
			}
		}

		# Stripslashes as shown on http://php.net/get_magic_quotes_gpc
		# Used to prevent remote code injection via post, get, cookie. Also contributes to prevent remote code injection via SQL since input is cleaned prior to inserting to SQL DB.
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
		
	# Include essential files
	#-------------

		require_once 'db.php';
		require_once 'functions.php';
		
	# Block out unwelcome visitors
	#-------------

		$userid = GetUserIDFromSession($ip);

	# REQUEST: Get our requested page and set the variable
	#-------------

                if (isset($_REQUEST["op"]) && is_string($_REQUEST["op"])) {
  		  $op = $_REQUEST['op'];
                } else {
                  $op = "";
                }

		# Prevent infinite loop bug, bug found by Jailout2000
		if($op == 'index') $op = '';

	# BLIZZARD: Is this Blizzard, and if so, log it
	#-------------
	
		if(isblizzard($ip)){
			#IP identified as potentially belonging to blizzard. Note it in log.
			logthis(-1, 'Blizzard accessed page: ?'.$_SERVER['QUERY_STRING'], 'vip');
		}

	# SECURITY: Prevent remote inclusion injection
	#-------------
	
		$legal_inc=getdir('.');

		$i=0;
		$allowfile='false';
		while($i <= count($legal_inc)){
			if($op.'.php'==$legal_inc[$i]) $allowfile='true';
			$i++;
		}
		
		if(!$op || trim($op) == '') $allowfile='true';
		
		if($allowfile=='false') exit();

        # New Relic Transactions
        #-------------

                if (extension_loaded('newrelic')) {
                  if ($op == "") {
                    newrelic_name_transaction("/news.php");
                  } else {
                    newrelic_name_transaction("/" . $op . ".php");
                  }
                  newrelic_add_custom_parameter("REMOTE_ADDR", $_SERVER["REMOTE_ADDR"]);
                }

	# Surpress code is in place to prevent a HEADER ALREADY SENT error.
	#-------------

		ob_start('ob_gzhandler');
		
		if($surpress != "true"){
			if($op == "") $op = "news";
			include "bdif/header.dm";
			if($userid){
				# Update member last login
				mysql_query("UPDATE sessions SET dtstamp=NOW() WHERE id='$userid'") or die("UpdateSession Function Error: ".mysql_error());
				$msg = GetData($userid, 'msg');
			} else {
				$msg = $_REQUEST['msg'];
				if($msg == '1'){
					$msg = "You have been logged out.";
				} elseif($msg == ''){
					#disregard
				} else {
					$msg = "Nice try buddy!";
				}
			}
			
			if($msg){
				$msg = '<center><font color=lime><b>'.$msg.'</b></font></center>';
				WriteData($userid, 'msg', '');
				echo "<br>";
				DisplayDialogBox($msg);
				echo "<br>";
			}
			$op=$op.".php";
			include $op;
			include "bdif/footer.dm";
		} else {
			if($op != ""){
				$op=$op.".php";
				include $op;
			} else {
				echo 'op empty.';
			}
		}

		mysql_close();
		ob_end_flush();
		exit();

	#-------------
	# End of main script.
	#-------------
?>

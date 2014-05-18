<?
	################################
	##	RSS Feed Generator for logs   ##
	################################
	
	###########################################################
	# ADDED 2014-04-06 BY JAILOUT2000 FOR FORCED SSL SECURITY #
	###########################################################
	if($_SERVER['SERVER_PORT']!=443 || $_SERVER['HTTP_HOST']!='bnetdocs.org') {
		http_response_code(301);
		header('Location: https://bnetdocs.org'.$_SERVER['REQUEST_URI']);
		exit;
	}
	###########################################################
	#                                                         #
	###########################################################
	
	# \n = newline, \t = tab
	
	function CropSentence ($strText, $intLength, $strTrail) {
		$wsCount = 0;
		$intTempSize = 0;
		$intTotalLen = 0;
		$intLength = $intLength - strlen($strTrail);
		$strTemp = "";

		if (strlen($strText) > $intLength) {
			$arrTemp = explode(" ", $strText);
			foreach ($arrTemp as $x) {
				if (strlen($strTemp) <= $intLength) $strTemp .= " " . $x;
			}
			$CropSentence = $strTemp . $strTrail;
		} else {
			$CropSentence = $strText;
		}

		return $CropSentence;
	} 
	
	function replaceOnce($search, $replace, $content){
	    $pos = strpos($content, $search) + 1;
		$pos = strpos($content, $search, $pos);
	    if ($pos === false) { 
			return $content; 
		} else { 
			return substr($content, 0, $pos) . $replace . substr($content, $pos+strlen($search)); 
		}
	}

	$auth = 'true';
	include 'func.php';
	include 'db.php';

	###########################################################
	# ADDED 2014-03-25 BY JAILOUT2000 FOR SECURITY HOLE PATCH #
	###########################################################
	$_AUTH_USER = sanitize($_SERVER['PHP_AUTH_USER']);
	$_AUTH_PASS = sanitize($_SERVER['PHP_AUTH_PW']);
	$_AUTH_QUERY = 'SELECT `password`, `levelofaccess` FROM `users` WHERE `username` = \''
                     . mysql_real_escape_string($_AUTH_USER) . '\' LIMIT 1;';
	$_AUTH_RESULT = mysql_query($_AUTH_QUERY);
	$_AUTH_PASS_1 = strtolower(md5($_AUTH_PASS));
	$_AUTH_PASS_2 = 'error'; // anything md5 will never match this since the string length is different
	$_AUTH_ACCESS_LEVEL = 0;
	if ($_AUTH_RESULT) {
		if (mysql_num_rows($_AUTH_RESULT) == 1) {
			$_AUTH_ROW = mysql_fetch_row($_AUTH_RESULT);
			$_AUTH_PASS_2 = strtolower($_AUTH_ROW[0]);
			$_AUTH_ACCESS_LEVEL = $_AUTH_ROW[1];
		}
	}
	if ($_AUTH_PASS_1 != $_AUTH_PASS_2 || $_AUTH_ACCESS_LEVEL < 3) {
		header('HTTP/1.0 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="BNETDocs Logs"');
		header('Content-Type: application/xml;charset=utf-8');
		die("<?xml version=\"1.0\" encoding=\"utf-8\"?><error>Access denied. Administrator login required.</error>");
	}
	/*$userid = GetUserIDFromSession($_SERVER['REMOTE_ADDR']);
	if(rank($userid) < 3) {
		http_response_code(401);
		header('Content-Type: application/xml;charset=utf-8');
		die('<?xml version="1.0" encoding="utf-8"?><error>Access denied. Administrator login required.</error>');
	}*/
	###########################################################
	#                                                         #
	###########################################################
	
	header("Content-Type: application/xml; charset=utf-8");
	
	echo '<?xml version="1.0" encoding="utf-8"?>';
	echo '<rss version="2.0">';
	echo '<channel>';
	echo '<title>BNETDocs Logs</title>';
	echo '<link>http://www.bnetdocs.org/?op=viewlogs</link>';
	echo '<description>Summarized logs of user activities on BNETDocs</description>';
	echo '<language>en-us</language>';
	echo '<docs>http://blogs.law.harvard.edu/tech/rss</docs>';
	echo '<copyright>Site scripts and design copyrights reserved to Don Cullen. Contents copyrighted to Blizzard and their parent corporation, Vivendi. Copyright infringements will be prosecuted to the fullest extent allowable by law. Please view our legal disclaimer and terms of service.</copyright>';

	$privatestring = "WHERE eventtype != 'CONFIDENTAL' ";
	$privatestring .= " group by CONCAT(user,datetime,eventtype) ";
	$consolidate = '&consolidate=true';
	
    $limit          = 25;               
    $query_count    = "SELECT * FROM logs $privatestring";
    $result_count   = mysql_query($query_count);
    $totalrows      = mysql_num_rows($result_count); 
	
	$query  = "SELECT * FROM logs $privatestring ORDER BY id desc LIMIT $limit";
	
    $result = mysql_query($query) or die("Error: " . mysql_error()); 

	while($row = mysql_fetch_array($result)){
	
		echo '<item>';
		
		$summary = CropSentence(strip_tags(codify($row['event'], true) ), 500, '...');
		
		if($row['eventtype'] == 'docedit') $row['event'] = 'edited documents.';
		if($row['eventtype'] == 'newsdelete') $row['event'] = 'deleted news posts.';
		if($row['eventtype'] == 'newsedit') $row['event'] = 'edited news posts.';
		if($row['eventtype'] == 'newspost') $row['event'] = 'submitted news posts.';
		if($row['eventtype'] == 'pktadd') $row['event'] = 'added packets.';
		if($row['eventtype'] == 'pktedit') $row['event'] = 'edited packets.';
		if($row['eventtype'] == 'pktdel') $row['event'] = 'deleted packets.';
		if($row['eventtype'] == 'registration') $row['event'] = 'registered to be a member.';
		if($row['eventtype'] == 'commentedit') $row['event'] = 'edited comments.';
		if($row['eventtype'] == 'commentadd') $row['event'] = 'posted comments.';
		if($row['eventtype'] == 'commentdel') $row['event'] = 'deleted comments.';
		
		$content = CropSentence(strip_tags(codify(str_ireplace("\'", "'", $row['event']), true) ), 500, '...');
		$summary = CropSentence(strip_tags(codify(str_ireplace("\'", "'", $summary), true) ), 500, '...');
		
		$initator = $row['user'];
		
		$content = ucfirst(whoisid($initator)).' '.lcfirst($content);
		
		$timestamp = date("D, d M Y H:i:s", strtotime(substr($row['datetime'], strpos($row['datetime'], ',') + 2))).' PST';
		$timestamp = substr($row['datetime'], strpos($row['datetime'], ',') + 2).' PST';
		$timestamp = replaceOnce(' ', ', ', $timestamp);
		$timestamp = date("D, d M Y H:i:s", strtotime($timestamp)).' PST';

		echo '<title>'.htmlspecialchars ($content, ENT_QUOTES).'</title>';
		echo '<link>http://www.bnetdocs.org/?op=viewlogs&amp;id='.$row['id'].'</link>';
		echo '<guid>http://www.bnetdocs.org/?op=viewlogs&amp;id='.$row['id'].'</guid>';
		echo '<description>'.htmlspecialchars ($summary, ENT_QUOTES).' Activity logged at: '.$row['datetime'].'</description>';
		echo '<pubDate>'.$timestamp.'</pubDate>';
		echo '</item>';
	
    }

    mysql_free_result($result);

	echo '</channel>';
	echo '</rss>';
?>

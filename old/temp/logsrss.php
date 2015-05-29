<?
	################################
	##	RSS Feed Generator for logs   ##
	################################
	
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
	include 'functions.php';
	include 'db.php';
	
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

	$privatestring = "WHERE eventtype != 'CONFIDENTAL' AND eventtype != 'vip' AND eventtype != 'registration' AND eventtype != 'account' AND eventtype != 'hack'";
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
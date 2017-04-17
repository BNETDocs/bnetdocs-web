<?
	################################
	##	RSS Feed Generator for news ##
	################################

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

	$auth = 'true';
	require_once 'functions.php';
	require_once 'db.php';
	header("Content-Type: application/xml; charset=utf-8");
	echo '<?xml version="1.0" encoding="utf-8"?>';
	echo '<rss version="2.0">';
	echo '<channel>';
	echo '<title>BNETDocs News</title>';
	echo '<link>http://www.bnetdocs.org/</link>';
	echo '<description>Battle.Net Logon Sequences, Packets, information and protocols reference site.</description>';
	echo '<language>en-us</language>';
	echo '<docs>http://blogs.law.harvard.edu/tech/rss</docs>';
	echo '<copyright>Site scripts and design copyrights reserved to Don Cullen. Contents copyrighted to Blizzard and their parent corporation, Vivendi. Copyright infringements will be prosecuted to the fullest extent allowable by law. Please view our legal disclaimer and terms of service.</copyright>';
	
	$sqlquery = 'SELECT * FROM news ORDER BY id DESC';
	$newsarray = mysql_query($sqlquery);
	
	while($row = mysql_fetch_array($newsarray)){
		echo '<item>';
		$nid = $row['id'];
		$author = $row['poster'];
		$rank = rank($author);
		if($rank){
			$rank = '('.rankname($rank).')';
		} else {
			$rank = '';
		}
		$dtstamp = $row['dtstamp'];
		$postdate = date('F j, Y g:i T', strtotime($dtstamp));
		$icon = strtolower($row['topictype']);
		$title = strip_tags($row['subject']);
		$content = CropSentence(strip_tags(codify($row['content'], true) ), 500, '...');
		echo '<title>'.htmlspecialchars ($title, ENT_XML1 | ENT_QUOTES | ENT_DISALLOWED).'</title>';
		echo '<link>http://www.bnetdocs.org/?op=news&amp;nid='.$nid.'</link>';
		echo '<description>'.htmlspecialchars ($content, ENT_XML1 | ENT_QUOTES | ENT_DISALLOWED).'</description>';
		echo '<guid>http://www.bnetdocs.org/?op=news&amp;nid='.$nid.'</guid>';
		echo '</item>';
	} 
	echo '</channel>';
	echo '</rss>';
?>

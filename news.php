<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	$mode = sanitize($_GET['mode']);
	$newsicon = sanitize(strtoupper($_POST['pictureselector']));
	$title = sanitize($_POST['subject']);
	$content = sanitize($_POST['text']);
	
	if(!$_POST){
		$title = 'Insert news title here.';
		$content = 'Insert news here.';
		if($mode == 'all'){
			$sqlquery = 'SELECT * FROM news ORDER BY id DESC';
			$newsarray = mysql_query($sqlquery);
			while($row = mysql_fetch_array($newsarray)){
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
				$title = $row['subject'];
				$content = codify($row['content']);
				include 'bdif/newspost.dm';
			}
		} elseif($mode == 'edit'){
			$nid = $_GET['nid'];
                        if (!is_numeric($nid)) {
                                logthis($userid, 'Illegally attempted to edit a news post with invalid NID. Attempt blocked.', 'hack');
                                blockhack();
                        }
			$pid = GetInfo("news", "id", $nid, "poster");
			if($userid != $pid && $userid != 1){
				logthis($userid, 'Illegally attempted to edit a news post NID('.$nid.'). Attempt blocked.', 'hack');
				blockhack();
			}
			$sqlquery = 'SELECT * FROM news WHERE id='.mysql_real_escape_string($nid).' ORDER BY id DESC LIMIT 1';
			$newsarray = mysql_query($sqlquery);
			$row = mysql_fetch_array($newsarray);
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
			$title = $row['subject'];
			$content = $row['content'];
			$topictype = strtolower($row['topictype']);
			switch ($topictype) {
				case 'bnetdocs':
				    $bdselected = 'selected';
				    break;
				case 'bnet':
				    $bnselected = 'selected';
				    break;
				case 'bnls':
				    $bnlsselected = 'selected';
				    break;
				case 'starcraft':
					$scselected = 'selected';
					break;
				case 'warcraft':
					$wcselected = 'selected';
					break;
				case 'diablo':
					$dselected = 'selected';
					break;	
			}
			$edit = '&edit=true&nid='.$nid;
			include 'bdif/newssubmission.dm'; 
		} elseif($mode == 'delete'){
			$nid = $_GET['nid'];
			if(rank($userid) > 2){
				$pid = GetInfo("news", "id", $nid, "poster");
				$title = GetInfo("news", "id", $nid, "subject");
				$content = GetInfo("news", "id", $nid, "content");
				$nid = $_GET['nid'];
				if((rank($userid) > 4) || ($userid == $pid)){
					$sql = "DELETE FROM news WHERE id=\"$nid\"";
					$sqlresults = mysql_query($sql) or die('News deletion failure: '.mysql_error());
					logthis($userid, 'Deleted a news post containing the title of "'.$title.'" and the contents of "'.$content.'".', 'newsdelete');
					WriteData($userid, 'msg', 'The news post has been deleted.');
					redirect('/');
				} else {
					logthis($userid, 'Illegally attempted to delete a news post NID('.$nid.'). Attempt blocked.', 'hack');
					blockhack();
				}
			} else {
				logthis($userid, 'Illegally attempted to delete a news post NID('.$nid.'). Attempt blocked.', 'hack');
				blockhack();
			}
		} elseif($mode == 'post'){
			if(rank($userid) > 2){
				$rank = rank($userid);
				if($rank){
					$rank = '('.rankname($rank).')';
				} else {
					$rank = '';
				}
				$postdate = date('F j, Y g:i T');
				include 'bdif/newssubmission.dm';
			} else {
				logthis($userid, 'Illegally attempted to post news. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			$rnid = $_REQUEST['nid'];
			if($rnid) {
				$sqlquery = 'SELECT * FROM news where id='.$rnid;
			}  else {
				$sqlquery = 'SELECT * FROM news ORDER BY id DESC LIMIT 3';
			}
			$newsarray = mysql_query($sqlquery);
			while($row = mysql_fetch_array($newsarray)){
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
				$title = $row['subject'];
				$content = codify($row['content']);
				include 'bdif/newspost.dm';
			} 
		}
	} else {
		$edit = $_GET['edit'];
		$nid = $_GET['nid'];
		if($edit){
			if(rank($userid) > 2){
				$pid = GetInfo("news", "id", $nid, "poster");
				if($userid == $pid || $userid == 1){
					if(!$title){
						msgbox('Title is required!');
						$title = 'Insert news title here.';
						include 'bdif/newssubmission.dm';
					} elseif(!$content){
						msgbox('Cannot submit blank news!');
						$content = 'Insert news here.';
						include 'bdif/newssubmission.dm';
					} else {
						mysql_query("UPDATE news SET topictype='$newsicon',subject='$title',content='$content',edited=1 WHERE id=$nid") or die("News Update Error: ".mysql_error());
						logthis($userid, 'Updated a news post (NID: '.$nid.') using the title of "'.$title.'" and the contents of "'.$content.'".', 'newsedit');
						WriteData($userid, 'msg', 'The news post has been updated.');
						redirect('/');
					}
				} else {
					logthis($userid, 'Illegally attempted to edit a news post NID('.$nid.'). Attempt blocked.', 'hack');
					blockhack();
				}
			} else {
				logthis($userid, 'Illegally attempted to edit a news post NID('.$nid.'). Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			if(rank($userid) > 2){
				if(!$title){
					msgbox('Title is required!');
					$title = 'Insert news title here.';
					include 'bdif/newssubmission.dm';
				} elseif(!$content){
					msgbox('Cannot submit blank news!');
					$content = 'Insert news here.';
					include 'bdif/newssubmission.dm';
				} else {
					mysql_query("INSERT INTO news (poster,topictype,subject,content) VALUES ('$userid','$newsicon', '$title', '$content')") or die("News Submission Error: ".mysql_error());
					logthis($userid, 'Posted to the news using the title of "'.$title.'" and the contents of "'.$content.'".', 'newspost');
					WriteData($userid, 'msg', 'News submission has been posted.');
					redirect('/');
				}
			} else {
				logthis($userid, 'Illegally attempted to post news. Attempt blocked.', 'hack');
				blockhack();
			}
		}
	}
?>

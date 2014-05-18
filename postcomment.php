<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie, $userid;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	if(!$userid) die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	$pid = sanitize($_POST['pid']);
	$did = sanitize($_POST['did']);
	$cid = sanitize($_REQUEST['cid']);
	
	if(!$pid) $pid = sanitize($_REQUEST['pid']);
	if(!$did) $did = sanitize($_REQUEST['did']);
	
	if(!$pid && !$did){
		error('Unable to process. Missing identificator.');
		return;
	}
	
	$mycomment = removeEvilTags(sanitize($_POST['mycomment']));
	
	if($_POST){
		$mode = $_REQUEST['mode'];
		if($mode){
			if($mode == 'edit'){
				if($cid){
					$queryid = "SELECT * FROM comments WHERE id=\"$cid\"";
					$identification = mysql_query($queryid);
					$posterid = mysql_result($identification,0,"posterid");
					if($posterid == $userid){
						if($mycomment){
							if($pid){
								#Process packet comment
								# Insert into comments database
								$sql = "UPDATE `comments` SET message='$mycomment' WHERE id=".$cid;
								mysql_query($sql) or die("Packet Comment Edit Error: ".mysql_error());
								logthis($userid, 'Edited a comment on '.getpacketlink($pid).' saying: '.$mycomment, 'commentedit');
								WriteData($userid, 'msg', 'Successfully edited your comment on the packet! Thanks!');
								redirect('/?op=packet&pid='.$pid);
							} else {
								#Process packet comment
								# Insert into comments database
								$sql = "UPDATE `comments` SET message='$mycomment' WHERE id=".$cid;
								mysql_query($sql) or die("Document Comment Edit Error: ".mysql_error());
								logthis($userid, 'Edited a comment on '.getdocumentlink($did).' saying: '.$mycomment, 'commentedit');
								WriteData($userid, 'msg', 'Successfully edited your comment on the document! Thanks!');
								redirect('/?op=doc&did='.$did);
							}
						} else {
							error('Cannot submit blank comment.');
							return;
						}
					} else {
						error('You\'re not authorized to edit the comment.');
						return;
					}
				} else {
					error('Unable to process. Missing Comment ID.');
					return;
				}
			} else {
				error('Unable to process.');
				return;
			}
		} else {
			if($mycomment){
				if(!$pid && !$did){
					error('Unable to process.');
					return;
				} else {
					if($pid){
						#Process packet comment
						# Insert into comments database
						$sql = "INSERT INTO `comments` (posterid, pdid, message) VALUES ($userid, $pid, '$mycomment')";
						mysql_query($sql) or die("Packet Comment Add Error: ".mysql_error());
						logthis($userid, 'Commented on '.getpacketlink($pid).' saying: '.$mycomment, 'commentadd');
						WriteData($userid, 'msg', 'Successfully commented on the packet! Thanks!');
						redirect('/?op=packet&pid='.$pid);
					} else {
						#Process document comment
						$sql = "INSERT INTO `comments` (posterid, pdid, message) VALUES ($userid, $did, '$mycomment')";
						mysql_query($sql) or die("Document Comment Add Error: ".mysql_error());
						logthis($userid, 'Commented on '.getdocumentlink($did).' saying: '.$mycomment, 'commentadd');
						WriteData($userid, 'msg', 'Successfully commented on the document! Thanks!');
						redirect('/?op=doc&did='.$did);
					}
				}
			} else {
				error('Cannot submit blank comment.');
				return;
			}
		}
	} else {
		$mode = $_REQUEST['mode'];
		if($mode == 'edit'){
			if($cid){
				$queryid = "SELECT * FROM comments WHERE id=\"$cid\"";
				$identification = mysql_query($queryid);
				$message = mysql_result($identification,0,"message");
				$posterid = mysql_result($identification,0,"posterid");
				if($posterid == $userid){
					?>
					<div id="container">
					<div id="main2">
					<h2>Comment Editor</h2>
					<center>
					<br>
					<form method="POST" name="newcomment" action="/?op=postcomment&mode=edit&cid=<?=$cid;?>">
					<?
					if($pid){
						?><input type="hidden" name="pid" value="<?=$pid;?>"><?
					} else {
						?><input type="hidden" name="did" value="<?=$did;?>"><?
					}
					?>
					<textarea name="mycomment" style="width: 90%;" id="inputbox" rows="10" cols="81"><?=$message;?></textarea>
					<br><br><input type="submit" id="abutton" style="width:900" value="Submit"><br><br></form>
					</center></div>
					</div>
					<?
				} else {
					error('You\'re not authorized to edit the comment.');
					return;
				}
			} else {
				error('Unable to process. Missing Comment ID.');
				return;
			}
		} elseif($mode == 'delete'){
			$queryid = "SELECT * FROM comments WHERE id=$cid";
			$identification = mysql_query($queryid);
			$posterid = mysql_result($identification,0,"posterid");
			$message = mysql_result($identification,0,"message");
			if($posterid == $userid || rank($userid) > 2){
				#Process comment delete order
				$sql = "DELETE FROM comments WHERE id=$cid";
				mysql_query($sql) or die("Comment Delete Error: ".mysql_error());
				if($pid){
					$location = getpacketlink($pid).' posted by '.whoisid($posterid);
				} else {
					$location = getdocumentlink($did).' posted by '.whoisid($posterid);
				}
				logthis($userid, 'Deleted a comment on '.$location.' that had the message of: '.$message, 'commentdel');
				WriteData($userid, 'msg', 'Successfully deleted the comment.');
				if($did){
					redirect('/?op=doc&did='.$did);
				} else {
					redirect('/?op=packet&pid='.$pid);
				}
			} else {
				error('You\'re not authorized to delete this comment.');
				return;
			}
		} else {
			error('Unable to process.');
			return;
		}
	}
?>
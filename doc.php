<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');

	# Begin Code
	# -------------

	$did = sanitize($_GET['did']);
	$mode = sanitize($_GET['mode']);
	$action = sanitize($_GET['action']);
	$messageid = '';
	$messagename = '';

	if($mode == 'add'){
		#Check rank
		if($userid){
			if(rank($userid) > 2){
				#Allowed to add, now draw form
				$action = 'add';
				include 'bdif/doceditor.dm';
			} else {
				#Not allowed to add
				logthis($userid, 'Illegally attempted to add a new document. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			logthis($userid, 'Illegally attempted to add a new document. Attempt blocked.', 'hack');
			blockhack();
		}
	} elseif($mode == 'edit'){
		$action = 'edit';
		#Check rank
		if($userid){
			if(rank($userid) > 2){
				#Allowed to edit, now draw form
				if(!$did){
					logthis($userid, 'Illegally attempted to edit a document. Attempt blocked.', 'hack');
					blockhack();
				}
				$title = delslash(GetInfo('documents', 'id', $did, 'title'));
				$content = GetInfo('documents', 'id', $did, 'contents');
				$related = GetInfo('documents', 'id', $did, 'related');
				$clearance = GetInfo('documents', 'id', $did, 'clearance');

				if($clearance == 0) $dcgueselected = 'selected';
				if($clearance == 1) $dcuseselected = 'selected';
				if($clearance == 2) $dcsupselected = 'selected';
				if($clearance == 3) $dcediselected = 'selected';
				if($clearance == 4) $dcadmselected = 'selected';

				$action = 'edit';
				include 'bdif/doceditor.dm';
			} else {
				#Not allowed to edit
				logthis($userid, 'Illegally attempted to edit a document. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			logthis($userid, 'Illegally attempted to edit a document. Attempt blocked.', 'hack');
			blockhack();
		}
	} elseif($mode == 'delete'){
		$action = 'delete';
		#Check rank
		if($userid){
			if(rank($userid) > 2){
				#Allowed to delete, now process deletion
				if(!$did){
					logthis($userid, 'attempted to delete a document without specifying document. Attempt blocked.', 'hack');
					blockhack();
				}
				$title = GetInfo('documents', 'id', $did, 'title');
				$content = GetInfo('documents', 'id', $did, 'contents');
				$related = GetInfo('documents', 'id', $did, 'related');
				$clearance = GetInfo('documents', 'id', $did, 'clearance');

				if(strlen($related) > 0){
					$linkarray = explode(',', $related);
					foreach($linkarray as $link){
						if($link){
							$link = trim(strtoupper($link));
							if(substr($link, 0, 1) == 'D' || substr($link, 0, 1) == 'P'){
								if(substr($link, 0, 1) == 'D') $table = 'documents';
								if(substr($link, 0, 1) == 'P') $table = 'packets';
								if(FieldVerify('id', $table, substr($link, 1))){
									# Link has been validated
									$letter = substr($link, 0, 1);
									if($letter == 'P'){
										$rpdbid = substr($link, 1);
										$rdid = GetInfo('packets', 'id', $rpdbid, 'messageid');
										$rpname = GetInfo('packets', 'id', $rpdbid, 'messagename');
										$rpdirection = GetInfo('packets', 'id', $rpdbid, 'direction');
										if($rpdirection == 0){
											$rpdirection = "&nbsp;(C->S)";
										} else {
											$rpdirection = "&nbsp;(S->C)";
										}
										$relatedlinkstring .= '<a href="/?op=packet&pid='.$rpdbid.'">['.$rdid.']&nbsp;'.$rpname.$rpdirection.'</a>, ';
									} else {
										$rpdbid = substr($link, 1);
										$title = GetInfo('documents', 'id', $rpdbid, 'title');
										$relatedlinkstring .= '<a href="/?op=doc&did='.$rpdbid.'">'.$title.'</a>, ';
									}
								}
							} else {
								$relatedlinkstring .= '<a href="'.$link.'">'.$link.'</a>, ';
							}
						}
					}	
					if(strlen($relatedlinkstring) > 0) $relatedlinkstring = substr_replace($relatedlinkstring,"",-2);
				}

				$logstring = 'Title: '.$title.'
Content: '.$content.'
Related: '.$relatedlinkstring;
				
				# Delete packet from database
				$sql = "DELETE FROM `documents` WHERE id=$did";
				mysql_query($sql) or die("Document Delete Error: ".mysql_error());

				# Finished adding, confirm addition and take user to new packet.
				logthis($userid, 'Deleted a document. Document Data: 
'.$logstring.'', 'docdel');
				WriteData($userid, 'msg', 'Successfully deleted the document! Thanks for your contribution!');
				redirect('/');
			} else {
				#Not allowed to delete
				logthis($userid, 'Illegally attempted to delete a document. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			logthis($userid, 'Illegally attempted to delete a document. Attempt blocked.', 'hack');
			blockhack();
		}
	} else {
		if($action){
			if($action == 'edit'){
				# Update
				# Obtain POST'ed data

				$title = removeEvilTags(sanitize($_POST['title']));
				$content = removeEvilTags(sanitize($_POST['content']));
				$mrelated = removeEvilTags(sanitize($_POST['mrelated']));
				$mclearance = removeEvilTags(sanitize($_POST['mclearance']));
				
				# Done obtaining data, now determine if person has authorization to add documents.

				if(rank($userid) < 3){
					logthis($userid, 'Illegally attempted to edit a document. Attempt blocked.', 'hack');
					blockhack();
				}

				# Person has authorization, process data and edit it

				if(!$title || !$content){
					#missing data, abort
					if(!$title) $title .= 'title, ';
					if(!$content) $content .= 'content, ';
					$missingdata = substr_replace($missingdata,"",-1);
					$missingdata = substr_replace($missingdata,"",-1);
					WriteData($userid, 'msg', 'Unable to edit document, missing data: '.$missingdata.' please fix.');
					redirect('/?op=doc&mode=edit&did='.$did);
				}
				
				if(strlen($mrelated) > 0){
					$linkarray = explode(',', $mrelated);
					foreach($linkarray as $link){
						if($link){
							$link = trim(strtoupper($link));
							if(substr($link, 0, 1) == 'D' || substr($link, 0, 1) == 'P'){
								if(substr($link, 0, 1) == 'D') $table = 'documents';
								if(substr($link, 0, 1) == 'P') $table = 'packets';
								if(FieldVerify('id', $table, substr($link, 1))){
									# Link has been validated
									$letter = substr($link, 0, 1);
									if($letter == 'P'){
										$rpdbid = substr($link, 1);
										$rdid = GetInfo('packets', 'id', $rpdbid, 'messageid');
										$rpname = GetInfo('packets', 'id', $rpdbid, 'messagename');
										$rpdirection = GetInfo('packets', 'id', $rpdbid, 'direction');
										if($rpdirection == 0){
											$rpdirection = "&nbsp;(C->S)";
										} else {
											$rpdirection = "&nbsp;(S->C)";
										}
										$relatedlinkstring .= '<a href="/?op=packet&pid='.$rpdbid.'">['.$rdid.']&nbsp;'.$rpname.$rpdirection.'</a>, ';
									} else {
										$rpdbid = substr($link, 1);
										$titlex = GetInfo('documents', 'id', $rpdbid, 'title');
										$relatedlinkstring .= '<a href="/?op=doc&did='.$rpdbid.'">'.$titlex.'</a>, ';
									}
								}
							} else {
								$relatedlinkstring .= '<a href="'.$link.'">'.$link.'</a>, ';
							}
						}
					}	
					if(strlen($relatedlinkstring) > 0) $relatedlinkstring = substr_replace($relatedlinkstring,"",-2);
				}
				
				$oldtitle = GetInfo('documents', 'id', $did, 'title');
				$oldcontent = GetInfo('documents', 'id', $did, 'contents');
				$oldrelated = GetInfo('documents', 'id', $did, 'related');
				$oldclearance = GetInfo('documents', 'id', $did, 'clearance');
				
				if(strlen($oldrelated) > 0){
					$linkarray = explode(',', $oldrelated);
					foreach($linkarray as $link){
						if($link){
							$link = trim(strtoupper($link));
							if(substr($link, 0, 1) == 'D' || substr($link, 0, 1) == 'P'){
								if(substr($link, 0, 1) == 'D') $table = 'documents';
								if(substr($link, 0, 1) == 'P') $table = 'packets';
								if(FieldVerify('id', $table, substr($link, 1))){
									# Link has been validated
									$letter = substr($link, 0, 1);
									if($letter == 'P'){
										$rpdbid = substr($link, 1);
										$rdid = GetInfo('packets', 'id', $rpdbid, 'messageid');
										$rpname = GetInfo('packets', 'id', $rpdbid, 'messagename');
										$rpdirection = GetInfo('packets', 'id', $rpdbid, 'direction');
										if($rpdirection == 0){
											$rpdirection = "&nbsp;(C->S)";
										} else {
											$rpdirection = "&nbsp;(S->C)";
										}
										$oldrelatedlinkstring .= '<a href="/?op=packet&pid='.$rpdbid.'">['.$rdid.']&nbsp;'.$rpname.$rpdirection.'</a>, ';
									} else {
										$rpdbid = substr($link, 1);
										$titlex = GetInfo('documents', 'id', $rpdbid, 'title');
										$oldrelatedlinkstring .= '<a href="/?op=doc&did='.$rpdbid.'">'.$titlex.'</a>, ';
									}
								}
							} else {
								$oldrelatedlinkstring .= '<a href="'.$link.'">'.$link.'</a>, ';
							}
						}
					}	
					if(strlen($oldrelatedlinkstring) > 0) $oldrelatedlinkstring = substr_replace($oldrelatedlinkstring,"",-2);
				}
				
				$olddatalogstring = 'Title: '.$oldtitle.'
Content: '.$oldcontent.'
Clearance Level: '.$oldclearance.'
Related: '.$oldrelatedlinkstring;

				$newdatalogstring = 'Title: '.$title.'
Content: '.$content.'
Clearance Level: '.$clearance.'
Related: '.$relatedlinkstring;

				$newdatalogstring = sanitize($newdatalogstring);
				$title = mysql_real_escape_string($title);
				$content = $content;
				
				$sql = "UPDATE `documents` SET contents='$content', title='$title', related='$mrelated', clearance=$mclearance WHERE id=$did";
				mysql_query($sql) or die("Document Edit Error: ".mysql_error()); #aEdit

				# Finished adding, confirm addition and take user to updated document.
				WriteData($userid, 'msg', 'Successfully edited the document! Thanks for your contribution!');					
				logthis($userid, 'Edited a document. 
Old Document Data: 

'.$olddatalogstring.'

Updated Document Data:

'.$newdatalogstring.'', 'docedit');

				redirect('/?op=doc&did='.$did);
			} elseif($action == 'add') {
				# Add
				# Obtain POST'ed data

				$title = sanitize($_POST['title']);
				$content = sanitize($_POST['content']);
				$mrelated = sanitize($_POST['mrelated']);
				$mclearance = sanitize($_POST['mclearance']);

				# Done obtaining data, now determine if person has authorization to add documents.

				if(rank($userid) < 3){
					logthis($userid, 'Illegally attempted to add a document. Attempt blocked.', 'hack');
					blockhack();
				}

				# Person has authorization, process data and edit it

				if(!$title || !$content){
					#missing data, abort
					if(!$title) $title .= 'title, ';
					if(!$content) $content .= 'content, ';
					$missingdata = substr_replace($missingdata,"",-1);
					$missingdata = substr_replace($missingdata,"",-1);
					WriteData($userid, 'msg', 'Unable to add document, missing data: '.$missingdata.' please fix.');
					redirect('/?op=doc&mode=add');
				}

				if(strlen($mrelated) > 0){
					$linkarray = explode(',', $mrelated);
					foreach($linkarray as $link){
						if($link){
							$link = trim(strtoupper($link));
							if(substr($link, 0, 1) == 'D' || substr($link, 0, 1) == 'P'){
								if(substr($link, 0, 1) == 'D') $table = 'documents';
								if(substr($link, 0, 1) == 'P') $table = 'packets';
								if(FieldVerify('id', $table, substr($link, 1))){
									# Link has been validated
									$letter = substr($link, 0, 1);
									if($letter == 'P'){
										$rpdbid = substr($link, 1);
										$rdid = GetInfo('packets', 'id', $rpdbid, 'messageid');
										$rpname = GetInfo('packets', 'id', $rpdbid, 'messagename');
										$rpdirection = GetInfo('packets', 'id', $rpdbid, 'direction');
										if($rpdirection == 0){
											$rpdirection = "&nbsp;(C->S)";
										} else {
											$rpdirection = "&nbsp;(S->C)";
										}
										$relatedlinkstring .= '<a href="/?op=packet&pid='.$rpdbid.'">['.$rdid.']&nbsp;'.$rpname.$rpdirection.'</a>, ';
									} else {
										$rpdbid = substr($link, 1);
										$title = GetInfo('documents', 'id', $rpdbid, 'title');
										$relatedlinkstring .= '<a href="/?op=doc&did='.$rpdbid.'">'.$title.'</a>, ';
									}
								}
							} else {
								$relatedlinkstring .= '<a href="'.$link.'">'.$link.'</a>, ';
							}
						}
					}	
					if(strlen($relatedlinkstring) > 0) $relatedlinkstring = substr_replace($relatedlinkstring,"",-2);
				}
					
				$newdatalogstring = 'Title: '.$title.'
Content: '.$content.'
Clearance Level: '.$clearance.'
Related: '.$relatedlinkstring;

				$newdatalogstring = sanitize($newdatalogstring);

				$sql = "INSERT INTO `documents` (`contents`, `title`, `related`, `clearance`) VALUES ('$content', '$title', '$mrelated', $mclearance)";
				mysql_query($sql) or die("Document Add Error: ".mysql_error());

				# Finished adding, confirm addition and take user to updated document.
				WriteData($userid, 'msg', 'Successfully added the document! Thanks for your contribution!');					
				logthis($userid, 'Added a document. 
New Document Data: 

'.$newdatalogstring.'', 'docedit');
				$sql = "SELECT id FROM `documents` WHERE `contents` = '$content' AND `title` = '$title'";
				$sqlquery = mysql_query($sql) or die("Document Addition Selection Error: ".mysql_error());
				$newdid = mysql_result($sqlquery,0,'id');
				redirect('/?op=doc&did='.$newdid);
			}
		} else {
			if(!$did){
				echo 'No document ID has been specified. Unable to seek document data.';
			} else {
				$title = delslash(GetInfo('documents', 'id', $did, 'title'));
				$content = codify(GetInfo('documents', 'id', $did, 'contents'));
				$related = GetInfo('documents', 'id', $did, 'related');
				$clearance = GetInfo('documents', 'id', $did, 'clearance');
				if(strlen($related) > 0){
					$linkarray = explode(',', $related);
					foreach($linkarray as $link){
						if($link){
							$link = trim(strtoupper($link));
							if(substr($link, 0, 1) == 'D' || substr($link, 0, 1) == 'P'){
								if(substr($link, 0, 1) == 'D') $table = 'documents';
								if(substr($link, 0, 1) == 'P') $table = 'packets';
								if(FieldVerify('id', $table, substr($link, 1))){
									# Link has been validated
									$letter = substr($link, 0, 1);
									if($letter == 'P'){
										$rpdbid = substr($link, 1);
										$rdid = GetInfo('packets', 'id', $rpdbid, 'messageid');
										$rpname = GetInfo('packets', 'id', $rpdbid, 'messagename');
										$rpdirection = GetInfo('packets', 'id', $rpdbid, 'direction');
										if($rpdirection == 0){
											$rpdirection = "&nbsp;(C->S)";
										} else {
											$rpdirection = "&nbsp;(S->C)";
										}
										$relatedlinkstring .= '<a href="/?op=packet&pid='.$rpdbid.'">['.$rdid.']&nbsp;'.$rpname.$rpdirection.'</a>, ';
									} else {
										$rpdbid = substr($link, 1);
										$titlex = GetInfo('documents', 'id', $rpdbid, 'title');
										$relatedlinkstring .= '<a href="/?op=doc&did='.$rpdbid.'">'.$titlex.'</a>, ';
									}
								}
							} else {
								$relatedlinkstring .= '<a href="'.$link.'">'.$link.'</a>, ';
							}
						}
					}	
					if(strlen($relatedlinkstring) > 0) $relatedlinkstring = substr_replace($relatedlinkstring,"",-2);
				}

				if($clearance > 0){
					if($userid){
						if(rank($userid) < $clearance){
							logthis($userid, ' attempted to access a packet with insufficent clearance. Attempt blocked.', 'hack');
							blockhack();
						}
					} else {
						logthis($userid, ' attempted to access a packet with insufficent clearance. Attempt blocked.', 'hack');
						blockhack();
					}
				}
				
				$content = str_replace("(FILETIME)", "<font class=\"keyword\">(FILETIME)</font>", $content);
				$content = str_replace("(DWORD)", "<font class=\"keyword\">(DWORD)</font>", $content);
				$content = str_replace("(BOOLEAN)", "<font class=\"keyword\">(BOOLEAN)</font>", $content);
				$content = str_replace("(WORD)", "<font class=\"keyword\">(WORD)</font>", $content);
				$content = str_replace("(BYTE)", "<font class=\"keyword\">(BYTE)</font>", $content);
				$content = str_replace("(STRING)", "<font class=\"keyword\">(STRING)</font>", $content);
				$content = str_replace("(WIDESTRING)", "<font class=\"keyword\">(WIDESTRING)</font>", $content);
				$content = str_replace("(VOID)", "<font class=\"keyword\">(VOID)</font>", $content);
				
				$content = brfix($content);
				
				?>
				<div id="container">
				<div id="main2">
				<h2><?=$title;?></h2>
				<br>
				<? 
				if($userid){
					if(rank($userid) > 2){
						?><center><font size=1>[ &nbsp; <a href="/?op=doc&mode=edit&did=<?=$did;?>">EDIT</a> &nbsp;
						| &nbsp; <a onClick="return confimdelete();" href="/?op=doc&mode=delete&did=<?=$did;?>">DELETE</a> &nbsp; ]</font><br><br></center>
						<?
					}
				}
				?>
				<div align="center"><div style="width: 97%;" align="left">
				<?=$content;?>
				<br><br>
				<div align=center>
				<?
				if($related){
					?>
					<div id="code">Related: <?=$relatedlinkstring;?></div></div>
					<?
				}
				?></div></div></div></div></div>

				<div id="comments">
				<div id="container">
				<div id="main2">
				<h2>User Comments</h2>
				<center><br>For detailed questions and discussion, visit the <a href="http://forum.valhallalegends.com/index.php/board,17.0.html" target="_blank">Battle.net Research Forum</a><br><br></center>
<?
                                global $sql_connection;
				$sqlqueryz = 'SELECT id,posterid,UNIX_TIMESTAMP(dtstamp) as unixdtstamp,message FROM comments WHERE pdid=\''.mysqli_real_escape_string($sql_connection,$did).'\' ORDER BY id';
				$docarray = mysqli_query($sql_connection,$sqlqueryz);
				$commentcount = 0;
				while($rowz = mysqli_fetch_array($docarray)){
					$cid = $rowz['id'];
					$posterid = $rowz['posterid'];
					$dtstamp = date("M d, Y", $rowz['unixdtstamp']);
					$dtstamp .= '<br>'.date('h:i A', $rowz['unixdtstamp']);
					$message = Codify($rowz['message']);
					if($userid){
						if($posterid == $userid){
							$editdelstr = '[ <a href="/?op=postcomment&mode=edit&cid='.$cid.'&did='.$did.'">EDIT</a> | <a onClick="return confimdelete();" href="/?op=postcomment&mode=delete&cid='.$cid.'&did='.$did.'">DELETE</a> ]';
						} else {
							if(rank($userid) > 2){ 
								$editdelstr = '[ <a onClick="return confimdelete();" href="/?op=postcomment&mode=delete&cid='.$cid.'&did='.$did.'">DELETE</a> ]';
							}
						}
					}
					if($editdelstr){
						$title = '<div id="author" nowrap>'.$editdelstr.'</div>';
					} else {
						$title = '<div id="author" nowrap> </div>';
					}
					echo $title;
					echo '<table border=0 width="100%" cellpadding=1 cellspacing=0><tr><td width="20%" align="center" height="100%"><table height="100%" width="100%" class="commentsidebar" border=0 cellpadding=0 cellspacing=0><tr><td width="100%" height="100%" align="center" valign="center">'.whoisid($posterid).'<br>'.$dtstamp.'</td></tr></table></td><td><div style="border: 1px #555 solid; padding: 2px; padding-left: 5px;"><br>'.$message.'<br><br></div></td></tr></table>';
					$commentcount++;
				}
				if($commentcount < 1) echo '<center>No comments were made. Be the first to contribute!<br><br></center>';
				if($userid){
					?>
					<div id="author" nowrap><center>Got Something To Say?</center></div>
					<center>
					<br>
					<form method="POST" name="newcomment" action="/?op=postcomment">
					<input type="hidden" name="did" value="<?=$did;?>">
					<textarea name="mycomment" style="width: 90%;" id="inputbox" rows="10" cols="81"></textarea>
					<br><br><input type="submit" id="abutton" style="width:900" value="Submit"><br><br></form>
			
					</center></div>
					</div></div>
					<?
				}
			}
		}
	}
?>

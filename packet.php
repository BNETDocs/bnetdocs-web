<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');

	# Begin Code
	# -------------

	$qjtarget = sanitize($_POST['quickjump']);

	if(!$qjtarget){
		$pid = sanitize($_GET['pid']);
	} else {
		$qjtarget = sanitize($qjtarget);
		$pid = GetInfo('packets', 'messagename', $qjtarget, 'id');
		if(!$pid) $pid = -1;
	}
	
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
				include 'bdif/packeteditor.dm';
			} else {
				#Not allowed to add
				logthis($userid, 'Illegally attempted to add a new packet. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			logthis($userid, 'Illegally attempted to add a new packet. Attempt blocked.', 'hack');
			blockhack();
		}
	} elseif($mode == 'edit'){
		$action = 'edit';
		#Check rank
		if($userid){
			if(rank($userid) > 2){
				#Allowed to edit, now draw form
				if(!$pid){
					logthis($userid, 'Illegally attempted to edit a packet. Attempt blocked.', 'hack');
					blockhack();
				}
				$messageid = GetInfo('packets', 'id', $pid, 'messageid');
				$messagename = GetInfo('packets', 'id', $pid, 'messagename');
				$direction = GetInfo('packets', 'id', $pid, 'direction');
				
				$usedby = GetInfo('packets', 'id', $pid, 'usedby');

				$usedby = str_replace('Warcraft III: TFT', '%a%', $usedby);
				$usedby = str_replace('Warcraft III', '%b%', $usedby);
				$usedby = str_replace('Starcraft Shareware', '%c%', $usedby);
				$usedby = str_replace('Starcraft Japanese', '%d%', $usedby);
				$usedby = str_replace('Starcraft Broodwar', '%e%', $usedby);
				$usedby = str_replace('Starcraft', '%f%', $usedby);
				$usedby = str_replace('Diablo Shareware', '%g%', $usedby);
				$usedby = str_replace('Diablo II LOD', '%h%', $usedby);
				$usedby = str_replace('Diablo II', '%i%', $usedby);
				$usedby = str_replace('Diablo I', '%j%', $usedby);
				$usedby = str_replace('Warcraft II', '%k%', $usedby);
				$usedby = str_replace('World of Warcraft', '%l%', $usedby);

				if(strstr($usedby, '%c%')) $cbscsw = 'checked';
				if(strstr($usedby, '%f%')) $cbsc = 'checked';
				if(strstr($usedby, '%e%')) $cbbw = 'checked';
				if(strstr($usedby, '%d%')) $cbjsc = 'checked';
				if(strstr($usedby, '%g%')) $cbdsw = 'checked';
				if(strstr($usedby, '%j%')) $cbdiablo = 'checked';
				if(strstr($usedby, '%i%')) $cbd2 = 'checked';
				if(strstr($usedby, '%h%')) $cbd2lod = 'checked';
				if(strstr($usedby, '%k%')) $cbwc2 = 'checked';
				if(strstr($usedby, '%b%')) $cbwc3 = 'checked';
				if(strstr($usedby, '%a%')) $cbwc3tft = 'checked';
				if(strstr($usedby, '%l%')) $cbwow = 'checked';

				$format = GetInfo('packets', 'id', $pid, 'format');
				$remarks = GetInfo('packets', 'id', $pid, 'remarks');
				$related = GetInfo('packets', 'id', $pid, 'related');

				$status = GetInfo('packets', 'id', $pid, 'status');

				if($status == 0) $psrawselected = 'selected';
				if($status == 1) $psnorselected = 'selected';
				if($status == 2) $psresselected = 'selected';
				if($status == 3) $psdefselected = 'selected';

				$clearance = GetInfo('packets', 'id', $pid, 'clearance');

				if($clearance == 0) $pcgueselected = 'selected';
				if($clearance == 1) $pcuseselected = 'selected';
				if($clearance == 2) $pcsupselected = 'selected';
				if($clearance == 3) $pcediselected = 'selected';
				if($clearance == 4) $pcadmselected = 'selected';

				$pgroupid = GetInfo('packets', 'id', $pid, 'pgroup');
				$action = 'edit';
				include 'bdif/packeteditor.dm';
			} else {
				#Not allowed to edit
				logthis($userid, 'Illegally attempted to edit a packet. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			logthis($userid, 'Illegally attempted to edit a packet. Attempt blocked.', 'hack');
			blockhack();
		}
	} elseif($mode == 'delete'){
		$action = 'delete';
		#Check rank
		if($userid){
			if(rank($userid) > 2){
				#Allowed to delete, now process deletion
				if(!$pid){
					logthis($userid, 'attempted to delete a packet without specifying packet. Attempt blocked.', 'hack');
					blockhack();
				}
				$messageid = GetInfo('packets', 'id', $pid, 'messageid');
				$messagename = GetInfo('packets', 'id', $pid, 'messagename');
				$direction = GetInfo('packets', 'id', $pid, 'direction');
				$direction = GetInfo('traffic', 'id', $direction, 'longdescr');
				$usedby = GetInfo('packets', 'id', $pid, 'usedby');
				if(!$usedby) $usedby = 'Unknown';
				$format = GetInfo('packets', 'id', $pid, 'format');
				$remarks = GetInfo('packets', 'id', $pid, 'remarks');
				$mrelated = GetInfo('packets', 'id', $pid, 'related');
				$pstatus = GetInfo('packets', 'id', $pid, 'status');
				
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
										$rpdirection = '&nbsp;(' . GetInfo('traffic', 'id', $rpdirection, 'shortcutlink') . ')';
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

				switch ($pstatus) {
					case 0:
						$pstatus = 'RAW, NEW PACKET';
						break;
					case 1: 
						$pstatus = '';
						break;
					case 2:
						$pstatus = 'MORE RESEARCH NEEDED';
						break;
					case 3:
						$pstatus = 'DEFUNCT';
						break;
				}
				
				$logstring = 'Message ID: '.$messageid.'
Message Name: '.$messagename.'
Message Status: '.$pstatus.'
Direction: '.$direction.'
Used By: '.$usedby.'
Format: '.$format.'
Remarks: '.$remarks.'
Related: '.$relatedlinkstring;
				
				# Delete packet from database
				$sql = "DELETE FROM `packets` WHERE id=$pid";
				mysql_query($sql) or die("Packet Delete Error: ".mysql_error());

				# Finished adding, confirm addition and take user to new packet.
				logthis($userid, 'Deleted a packet with the Message ID of '.$messageid.'. Contents of packet: 

'.$logstring.'', 'pktdel');
				WriteData($userid, 'msg', 'Successfully deleted the packet! Thanks for your contribution!');
				redirect('/');
			} else {
				#Not allowed to delete
				logthis($userid, 'Illegally attempted to delete a packet. Attempt blocked.', 'hack');
				blockhack();
			}
		} else {
			logthis($userid, 'Illegally attempted to delete a packet. Attempt blocked.', 'hack');
			blockhack();
		}
	} else {
		if($action){
			if($action == 'edit'){
				# Update
				# Obtain POST'ed data

				$mid = removeEvilTags(sanitize($_POST['mid']));
				$mname = removeEvilTags(sanitize($_POST['mname']));
				$mdirection = removeEvilTags($_POST['mdirection']);
				$mformat = removeEvilTags(sanitize($_POST['mformat']));
				$mremarks = removeEvilTags(sanitize($_POST['mremarks']));
				$mrelated = removeEvilTags(sanitize($_POST['mrelated']));
				$mstatus = removeEvilTags($_POST['mstatus']);
				$mclearance = removeEvilTags($_POST['mclearance']);
				$mgroup = removeEvilTags($_POST['mgroup']);

				$scsw = $_POST['scsw'];
				$bw = $_POST['bw'];
				$dsw = $_POST['dsw'];
				$d2 = $_POST['d2'];
				$wc2 = $_POST['wc2'];
				$wc3tft = $_POST['wc3tft'];
				$sc = $_POST['sc'];
				$jsc = $_POST['jsc'];
				$diablo = $_POST['diablo'];
				$d2lod = $_POST['d2lod'];
				$wc3 = $_POST['wc3'];
				$wow = $_POST['wow'];

				# Done obtaining data, now determine if person has authorization to add packets.

				if(rank($userid) < 3){
					logthis($userid, 'Illegally attempted to edit a packet. Attempt blocked.', 'hack');
					blockhack();
				}

				# Person has authorization, process data and add it

				if(!$mid || !$mname || !$mgroup){
					#missing data, abort
					if(!$mid) $missingdata .= 'message id, ';
					if(!$mname) $missingdata .= 'message name, ';
					if(!$mgroup) $missingdata .= 'group, ';
					$missingdata = substr_replace($missingdata,"",-1);
					$missingdata = substr_replace($missingdata,"",-1);
					WriteData($userid, 'msg', 'Failed to edit packet, missing data: '.$missingdata.'.');
					redirect('/?op=packet&mode=edit&pid='.$pid);
				}

				# Compile 'used by' string 
				
				$comma = '';
				if($scsw){
					$comma = ', ';
					$musedby .= 'Starcraft Shareware'.$comma;
				}
				if($bw){
					$comma = ', ';
					$musedby .= 'Starcraft Broodwar'.$comma;
				}
				if($dsw){
					$comma = ', ';
					$musedby .= 'Diablo Shareware'.$comma;
				}
				if($d2){
					$comma = ', ';
					$musedby .= 'Diablo II'.$comma;
				}
				if($wc2){
					$comma = ', ';
					$musedby .= 'Warcraft II'.$comma;
				}
				if($wc3tft){
					$comma = ', ';
					$musedby .= 'Warcraft III: TFT'.$comma;
				}
				if($sc){
					$comma = ', ';
					$musedby .= 'Starcraft'.$comma;
				}
				if($jsc){
					$comma = ', ';
					$musedby .= 'Starcraft Japanese'.$comma;
				}
				if($diablo){
					$comma = ', ';
					$musedby .= 'Diablo I'.$comma;
				}
				if($d2lod){
					$comma = ', ';
					$musedby .= 'Diablo II LOD'.$comma;
				}
				if($wc3){
					$comma = ', ';
					$musedby .= 'Warcraft III'.$comma;
				}
				if($wow) $musedby .= 'World of Warcraft';
				
				if($musedby[strlen($musedby)-2] == ','){
					$musedby = substr_replace($musedby,"",-1);
					$musedby = substr_replace($musedby,"",-1);
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
										$rpdirection = '&nbsp;(' . GetInfo('traffic', 'id', $rpdirection, 'shortcutlink') . ')';
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
					
				# Update packets database
				#$mname = sanitize($mname);
				#$mformat = sanitize($mformat);
				#$mremakrs = sanitize($mremarks);
				$sql = "UPDATE `packets` SET pgroup=$mgroup, messageid='$mid', messagename='$mname', direction=$mdirection, usedby='$musedby', format='$mformat', remarks='$mremarks', related='$mrelated', status=$mstatus, clearance=$mclearance WHERE id=$pid";
				mysql_query($sql) or die("Packet Edit Error: ".mysql_error());
				
				# Finished editing, confirm edition and take user to updated packet.
				$linkto = '<a href="/?op=packet&pid='.$pid.'">Link to packet</a>';
				logthis($userid, 'Edited a packet (['.$mid.'] '.$mname.'). '.$linkto, 'pktedit');
				WriteData($userid, 'msg', 'Successfully edited the packet! Thanks for your contribution!');

				redirect('/?op=packet&pid='.$pid);
			} elseif($action == 'add') {
				# Add Packet Data
				# Obtain POST'ed data

				$mid = removeEvilTags(sanitize($_POST['mid']));
				$mname = removeEvilTags(sanitize($_POST['mname']));
				$mdirection = removeEvilTags($_POST['mdirection']);
				$mformat = removeEvilTags(sanitize($_POST['mformat']));
				$mremarks = removeEvilTags(sanitize($_POST['mremarks']));
				$mrelated = removeEvilTags(sanitize($_POST['mrelated']));
				$mstatus = removeEvilTags($_POST['mstatus']);
				$mclearance = removeEvilTags($_POST['mclearance']);
				$mgroup = removeEvilTags($_POST['mgroup']);

				$scsw = $_POST['scsw'];
				$bw = $_POST['bw'];
				$dsw = $_POST['dsw'];
				$d2 = $_POST['d2'];
				$wc2 = $_POST['wc2'];
				$wc3tft = $_POST['wc3tft'];
				$sc = $_POST['sc'];
				$jsc = $_POST['jsc'];
				$diablo = $_POST['diablo'];
				$d2lod = $_POST['d2lod'];
				$wc3 = $_POST['wc3'];
				$wow = $_POST['wow'];

				# Done obtaining data, now determine if person has authorization to add packets.

				if(rank($userid) < 3){
					logthis($userid, 'Illegally attempted to add a packet. Attempt blocked.', 'hack');
					blockhack();
				}

				# Person has authorization, process data and add it

				if(!$mid || !$mname || !$mgroup){
					#missing data, abort
					if(!$mid) $missingdata .= 'message id, ';
					if(!$mname) $missingdata .= 'message name, ';
					if(!$mgroup) $missingdata .= 'group, ';
					$missingdata = substr_replace($missingdata,"",-1);
					$missingdata = substr_replace($missingdata,"",-1);
					WriteData($userid, 'msg', 'Failed to add packet, missing data: '.$missingdata.'.');
					redirect('/?op=packet&mode=add');
				}

				# Compile 'used by' string 
				
				$comma = '';
				if($scsw){
					$comma = ', ';
					$musedby .= 'Starcraft Shareware'.$comma;
				}
				if($bw){
					$comma = ', ';
					$musedby .= 'Starcraft Broodwar'.$comma;
				}
				if($dsw){
					$comma = ', ';
					$musedby .= 'Diablo Shareware'.$comma;
				}
				if($d2){
					$comma = ', ';
					$musedby .= 'Diablo II'.$comma;
				}
				if($wc2){
					$comma = ', ';
					$musedby .= 'Warcraft II'.$comma;
				}
				if($wc3tft){
					$comma = ', ';
					$musedby .= 'Warcraft III: TFT'.$comma;
				}
				if($sc){
					$comma = ', ';
					$musedby .= 'Starcraft'.$comma;
				}
				if($jsc){
					$comma = ', ';
					$musedby .= 'Starcraft Japanese'.$comma;
				}
				if($diablo){
					$comma = ', ';
					$musedby .= 'Diablo I'.$comma;
				}
				if($d2lod){
					$comma = ', ';
					$musedby .= 'Diablo II LOD'.$comma;
				}
				if($wc3){
					$comma = ', ';
					$musedby .= 'Warcraft III'.$comma;
				}
				if($wow) $musedby .= 'World of Warcraft';
				
				if($musedby[strlen($musedby)-2] == ','){
					$musedby = substr_replace($musedby,"",-1);
					$musedby = substr_replace($musedby,"",-1);
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
										$rpdirection = '&nbsp;(' . GetInfo('traffic', 'id', $rpdirection, 'shortcutlink') . ')';
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
				
				# Insert into packets database
				#$mname = sanitize($mname);
				#$mformat = sanitize($mformat);
				#$mremarks = sanitize($mremarks);
				
				$sql = "INSERT INTO `packets` (pgroup, messageid, messagename, direction, usedby, format, remarks, related, status, clearance) VALUES ($mgroup, '$mid', '$mname', $mdirection, '$musedby', '$mformat', '$mremarks', '$mrelated', $mstatus, $mclearance)";
				mysql_query($sql) or die("Packet Add Error: ".mysql_error());

				$sql = "SELECT id FROM `packets` WHERE messageid = '$mid' AND messagename = '$mname' AND direction = $mdirection";
				$sqlquery = mysql_query($sql) or die("Packet Addition Selection Error: ".mysql_error());
				$newpid = mysql_result($sqlquery,0,'id');

				# Finished adding, confirm addition and take user to new packet.
				$linkto = '<a href="/?op=packet&pid='.$newpid.'">Link to packet</a>';
				logthis($userid, 'Added a packet with the Message ID of '.$mid.'.', 'pktadd');
				logthis($userid, 'Added a packet (['.$mid.'] '.$mname.'). '.$linkto, 'pktadd');
				WriteData($userid, 'msg', 'Successfully added the packet! Thanks for your contribution!');
				$mode = '';
				
				redirect('/?op=packet'.$mode.'&pid='.$newpid);
			}
		} else {
			if(!$pid){
				error('No packet ID has been specified.');
			} else if(FieldVerify('id', 'packets', $pid) == false) {
				if($pid != -1){
					error('Invalid packet ID identifier.');
				} else {
					error('Unrecognized packet name.<br>Maybe you mistyped the name?');
				}
			} else {
				$messageid = GetInfo('packets', 'id', $pid, 'messageid');
				$messagename = GetInfo('packets', 'id', $pid, 'messagename');
				$direction = GetInfo('packets', 'id', $pid, 'direction');
				$direction = GetInfo('traffic', 'id', $direction, 'longdescr');
				$usedby = GetInfo('packets', 'id', $pid, 'usedby');

				$usedby = str_replace('Warcraft III: TFT', '%a%', $usedby);
				$usedby = str_replace('Warcraft III', '%b%', $usedby);
				$usedby = str_replace('Starcraft Shareware', '%c%', $usedby);
				$usedby = str_replace('Starcraft Japanese', '%d%', $usedby);
				$usedby = str_replace('Starcraft Broodwar', '%e%', $usedby);
				$usedby = str_replace('Starcraft', '%f%', $usedby);
				$usedby = str_replace('Diablo Shareware', '%g%', $usedby);
				$usedby = str_replace('Diablo II LOD', '%h%', $usedby);
				$usedby = str_replace('Diablo II', '%i%', $usedby);
				$usedby = str_replace('Diablo I', '%j%', $usedby);
				$usedby = str_replace('Warcraft II', '%k%', $usedby);
				$usedby = str_replace('World of Warcraft', '%l%', $usedby);
				$usedby = str_replace('%a%', '<img src="/images/games/w3xp.jpg" alt="Warcraft III: The Frozen Throne">', $usedby);
				$usedby = str_replace('%b%', '<img src="/images/games/war3.jpg" alt="Warcraft III">', $usedby);
				$usedby = str_replace('%c%', '<img src="/images/games/sware.jpg" alt="Starcraft Shareware">', $usedby);
				$usedby = str_replace('%d%', '<img src="/images/games/jsc.jpg" alt="Starcraft Japanese">', $usedby);
				$usedby = str_replace('%e%', '<img src="/images/games/starx.jpg" alt="Starcraft Broodwar">', $usedby);
				$usedby = str_replace('%f%', '<img src="/images/games/sc.jpg" alt="Starcraft">', $usedby);
				$usedby = str_replace('%g%', '<img src="/images/games/diablosw.jpg" alt="Diablo Shareware">', $usedby);
				$usedby = str_replace('%h%', '<img src="/images/games/d2exp.jpg" alt="Diablo">', $usedby);
				$usedby = str_replace('%i%', '<img src="/images/games/diablo2.jpg" alt="Diablo II">', $usedby);
				$usedby = str_replace('%j%', '<img src="/images/games/diablo.jpg" alt="Diablo">', $usedby);
				$usedby = str_replace('%k%', '<img src="/images/games/war2.jpg" alt="Warcraft II">', $usedby);
				$usedby = str_replace('%l%', '<img src="/images/games/wow.jpg" alt="World of Warcraft">', $usedby);

				$usedby = str_replace(', ', '<font color=black size=1>, </font>', $usedby);
				if(!$usedby) $usedby = '<font color=red>Unknown</font>';
				$format = codify(GetInfo('packets', 'id', $pid, 'format'), true);
				$remarks = codify(GetInfo('packets', 'id', $pid, 'remarks'), true);
				$mrelated = GetInfo('packets', 'id', $pid, 'related');
				$pstatus = GetInfo('packets', 'id', $pid, 'status');
				$pclearance = GetInfo('packets', 'id', $pid, 'clearance');
				
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
										$rpdirection = '&nbsp;(' . GetInfo('traffic', 'id', $rpdirection, 'shortcutlink') . ')';
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

				switch ($pstatus) {
					case 0:
						$pstatus = '<font color=red>RAW, NEW PACKET</font>';
						break;
					case 1: 
						$pstatus = '';
						break;
					case 2:
						$pstatus = '<font color=cyan>MORE RESEARCH NEEDED</font>';
						break;
					case 3:
						$pstatus = '<font color=red>DEFUNCT</font>';
						break;
				}
				
				if($pclearance > 0){
					if($userid){
						if(rank($userid) < $pclearance){
							logthis($userid, ' attempted to access a packet with insufficent clearance. Attempt blocked.', 'hack');
							blockhack();
						}
					} else {
						logthis($userid, ' attempted to access a packet with insufficent clearance. Attempt blocked.', 'hack');
						blockhack();
					}
				}
				$format = str_replace("(FILETIME)", "<font class=\"keyword\">(FILETIME)</font>", $format);
				$format = str_replace("(DWORD)", "<font class=\"keyword\">(DWORD)</font>", $format);
				$format = str_replace("(BOOLEAN)", "<font class=\"keyword\">(BOOLEAN)</font>", $format);
				$format = str_replace("(WORD)", "<font class=\"keyword\">(WORD)</font>", $format);
				$format = str_replace("(BYTE)", "<font class=\"keyword\">(BYTE)</font>", $format);
				$format = str_replace("(STRING)", "<font class=\"keyword\">(STRING)</font>", $format);
				$format = str_replace("(WIDESTRING)", "<font class=\"keyword\">(WIDESTRING)</font>", $format);
				$format = str_replace("(VOID)", "<font class=\"keyword\">(VOID)</font>", $format);
				
				$format = brfix($format);
				$remarks = brfix($remarks);
				
				?>
				<div id="container">
				<div id="main2">
				<h2>Packet Information</h2>
				<div align=center><br>
				<? 
				if($userid){
					if(rank($userid) > 2){ 
						?><font size=1>[ &nbsp; <a href="/?op=packet&mode=edit&pid=<?=$pid;?>">EDIT</a> &nbsp; 
						| &nbsp; <a onClick="return confimdelete();" href="/?op=packet&mode=delete&pid=<?=$pid;?>">DELETE</a> &nbsp; ]</font>
						<?
					}
				}
				?>
				<div align="center"><div style="width: 97%;" align="left">
				<table border=0 id="code" width=90%>
				<tr><td>Message ID:</td><td><?=$messageid;?></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td nowrap>Message Name:</td><td><?=$messagename;?></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<? if($pstatus){ ?><tr><td nowrap>Message Status:</td><td><?=$pstatus;?></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr><? } ?>
				<tr><td>Direction:</td><td><?=$direction;?></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td valign=top>Used By:</td><td><?=$usedby;?></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td valign=top>Format:</td><td><?=$format;?></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td valign=top>Remarks:</td><td><?=$remarks;?></td></tr>
				
				<? if($relatedlinkstring){ ?>
					<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
					<tr><td valign=top>Related:</td><td><?=$relatedlinkstring;?></td></tr>
				<? } ?>
				
				</table>
				</div>
				</div></div></div></div>

				<div id="comments">
				<div id="container">
				<div id="main2">
				<h2>User Comments</h2>
				<center><br>For detailed questions and discussion, visit the <a href="http://forum.valhallalegends.com/index.php/board,17.0.html" target="_blank">Battle.net Research Forum</a><br><br></center>
<?
                                global $sql_connection;
				$sqlqueryz = 'SELECT id,posterid,UNIX_TIMESTAMP(dtstamp) as unixdtstamp,message FROM comments WHERE pdid='.$pid.' ORDER BY id';
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
							$editdelstr = '[ <a href="/?op=postcomment&mode=edit&cid='.$cid.'&pid='.$pid.'">EDIT</a> | <a onClick="return confimdelete();" href="/?op=postcomment&mode=delete&cid='.$cid.'&pid='.$pid.'">DELETE</a> ]';
						} else {
							if(rank($userid) > 2){ 
								$editdelstr = '[ <a onClick="return confimdelete();" href="/?op=postcomment&mode=delete&cid='.$cid.'&pid='.$pid.'">DELETE</a> ]';
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
					<input type="hidden" name="pid" value="<?=$pid;?>">
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

<?
	global $catid, $subid, $userid;
	# If you're going to include this file, you'll need to predefine the $catid (category id) and the $subid (sub-category id) variables.
	
	if($catid && $subid){
		$sqlquery2 = 'SELECT * FROM comments WHERE catid='.$catid.' AND subid='.$subid.' ORDER BY id ASC';
		$result = mysql_query($sqlquery2);
		$numrows = mysql_num_rows($result);
		$rank = rank($author);
		if($rank > 1 && $rank != 6){
			#Assign Rank
			if($rank == 3) $rankname = '  <font size=1>(EDITOR)</font> ';
			if($rank == 4 || $rank == 5) $rankname = ' <font size=1>(ADMIN)</font> ';
			if($userid == $author) $edit = ' Edit |';
			if($rank > 2) $delete = ' Delete |';
		}

		$toggler = false;
		while($rowcomments = mysql_fetch_array($result)){
			if($toggler){
				$whichone = 'b';
				$toggler = false;
			} else {
				$whichone = 'a';
				$toggler = true;
			}
			$postdate1 = date('F jS, Y', strtotime($rowcomments['dtstamp']));
			$postdate2 = date('g:i A T', strtotime($rowcomments['dtstamp']));
			$postdate = $postdate1.'<br />'.$postdate2;
			$rank = rank($rowcomments['posterid']);
			if($rank > 1 && $rank != 6){
				#Assign Rank Icon
				if($rank == 2) $rankicon = '<img src="images/superusericon.png" /><br />';
				if($rank == 3) $rankicon = '<img src="images/editoricon.png" /><br />';
				if($rank == 4 || $rank == 5) $rankicon = '<img src="images/adminicon.png" /><br />';
				if($rank == 3) $rankname = '  <font size=1>(EDITOR)</font> ';
				if($rank == 4 || $rank == 5) $rankname = ' <font size=1>(ADMIN)</font> ';
			}
			#Generate options button based on the viewer's rank
			if($userid){
				$optionsbutton = '
					<ul class="cssbutton">
						<li>Options
							<div class="menu">
								<table>
									<tr>
										<td class="option"><a class="CellLink" href="/?op=profile&id='.$rowcomments['posterid'].'">View Profile</a></td>
									</tr>
									<tr>
										<td class="option"><a class="CellLink" href="/?op=messaging&operation=compose&id='.$rowcomments['posterid'].'">Send Message</a></td>
									</tr>';
				if($userid == $rowcomments['posterid']) {			#How to identify which comment to edit/delete? Also need to add operations for both in comments.php
					$optionsbutton .= '
									<tr>
										<td class="option"><a class="CellLink" href="/?op=comments&id='.$rowcomments['posterid'].'">Edit Comment</a></td>
									</tr>';
				}
				if(rank($userid) > 2){
					$optionsbutton .= '
									<tr>
										<td class="option"><a class="CellLink" href="/?op=profile&id='.$rowcomments['posterid'].'">Delete Comment</a></td>
									</tr>';
				}
				$optionsbutton .= '
								</table>
							</div>
						</li>
					</ul>';
			}
			
			$comments .= '<tr>
						<td class="commentauthor'.$whichone.'"><div class="commentauthor">'.whoisid($rowcomments['posterid']).'<br>'.$rankicon.'<font size=1>'.$postdate.'<br>'.$optionsbutton.'<br></font></div></td><td class="authorcommentdivider"><div>&nbsp;</div></td><td class="tri'.$whichone.'"><div class="spacer"></div></td><td class="thecomment'.$whichone.'"><div class="thecomment">'.$rowcomments['message'].'</div></td>
					</tr>';
		}
		$file=true;
		$text='if/comment.dm';
		$subid=$rnid;
		$misc=$comments;
		$title = '';
		include 'if/dialogbox.dm';
	} else {
		if(!$catid) echo '<font color=red><b>Comments.php Error: Missing Category ID.</b></font><br>';
		if(!$subid) echo '<font color=red><b>Comments.php Error: Missing Sub-Category ID.</b></font><br>';
	}
?>
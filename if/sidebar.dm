		</div>
		</td>
		<td id="sidebar" valign="top">
			<?
				# much bandwidth to monitor them.
				# Decided to disable redirector/server statuses, 
				# due to there being no need for them. Costs too
				# much bandwidth. Left it here in case I want to add a box to sidebar.
				/* if(GetOption($userid, 'showbrs') == 1) DisplayDialogBox('BNLS Redirector Status', 'Example Content', false, '', true);
				if(GetOption($userid, 'showbss') == 1) DisplayDialogBox('BNLS Server Status', 'Example Content', false, '', true);
				if(GetOption($userid, 'showbnss') == 1) DisplayDialogBox('Battle.net Server Status', 'Example Content', false, '', true);
				*/
				
				if(GetOption($userid, 'showrss') == 1) {
					$rssfeedshtml = '<span style="padding-left: 20px;"><a href="http://www.bnetdocs.org/newsrss.php"><img src="images/rss.gif" alt="" /> &nbsp; News</a></span><br />
									        <span style="padding-left: 20px;"><a href="http://www.bnetdocs.org/logsrss.php"><img src="images/rss.gif" alt="" /> &nbsp; Logs</a></span>';
					DisplayDialogBox('BNETDocs RSS Feeds', $rssfeedshtml, false, '', true);
				}
				$weight = 1;	# Increase weight to reduce frequency of priority ads being selected
				$totalads = mysql_num_rows(mysql_query("SELECT * FROM adsystem")); # Count ads
				if(rand()%$totalads > $weight) $priority = true;	# Check to see if we should choose priority ads
				if($priority){
					$result = mysql_query("SELECT * FROM adsystem WHERE priority=1 order by rand() limit 1");	# Choose random ad
				} else {
					$result = mysql_query("SELECT * FROM adsystem order by rand() limit 1");	# Choose random ad
				}
				$row = mysql_fetch_object($result);
				$imagepath = $row->imagepath;
				$url = $row->linkto;
				$ad = '<a href="'.$url.'" target="_blank"><img src="'.$imagepath.'" alt="" /></a>';
				DisplayDialogBox('Sponsors &amp; Affiliates', $ad, false, '', true);
			?>
		</td>
		</tr>
		</table>
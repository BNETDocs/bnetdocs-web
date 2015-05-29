<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	#Search BNETDocs
	$search = $_POST['searchquery'];
	if(!$search){
		?>
		<div id="container">
		<div id="main2">
		<h2>Search BNETDocs</h2>
		<form action="/?op=search" method="POST">
		<blockquote>
		<!-- Search For:<br>
		<input id="inputbox" type="text" size=55 name="searchquery">&nbsp;&nbsp;&nbsp;<input id="abutton" type="submit" value="Search"> -->
		<font color=red><b>This feature is incomplete, so it has been disabled.</b></font>
		</blockquote>
		<div id="author"><center>Factors to include in Search:</center></div>
		<br><div align=center><table border=0 cellpadding=0 cellspacing=0 width=80%><tr>
		<td width=100%>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="news">News <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="documents">Documents <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="bnetmsgs">Battle.net Messages <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="bnetudpmsgs">Battle.net UDP Messages <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="realmmsgs">Realm Messages <font color=red size=1> Incomplete</font><br>
		</td>
		<td width=0% nowrap>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="botnetmsgs">BotNet Messages <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="bnlsmsgs">BNLS Messages <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="d2gsmgs">D2GS Messages <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="comments">Comments <font color=red size=1> Incomplete</font><br>
		<INPUT TYPE=CHECKBOX checked value="yes" NAME="users">Users<br>
		</td></tr></table></div>
		</form><br>
		<div id="author"><center>Tips on Searching the BNETDocs:</center></div>
		<br><blockquote>
		The search engine will search through the specificed articles for the given keywords. 
		If you'd like specific articles to be searched, please select articles to factor in the search.<br>
		<br>
		AND, OR, NOT are recognized as valid operators.<br>
		</blockquote>
		<br>
		</div></div>
		<?
	} else {
		?>
		<div id="container">
		<div id="main2">
		<h2>BNETDocs Search Results</h2>
		<blockquote>
			<?
			extract($_POST);
		    echo 'Search Keywords Used: '.$search.'<br><br>';
		    $arrSearch = explode(" ", $search);
			if($news){															#Enable searching by poster
			    for ($i=0; $i<count($arrSearch); $i++) {
					if (strToUpper($arrSearch[$i])=='AND' or strToUpper($arrSearch[$i])=='OR' or strToUpper($arrSearch[$i])=='NOT') {
						if (strToUpper($arrSearch[$i])=='NOT') {
							if($i > 0){
								if (strToUpper($arrSearch[$i - 1])=='AND' or strToUpper($arrSearch[$i - 1])=='OR' or strToUpper($arrSearch[$i - 1])=='NOT'){
									# Append nothing. Operator already used.
								} else {
									$strWhere = $strWhere.' AND '; #ASSUME AND
								}	
							}
							$i++;
							$strWhere = $strWhere." content NOT LIKE '%".$arrSearch[$i]."%' ";
						} else {
							$strWhere = $strWhere." ".strToUpper($arrSearch[$i])." ";
						}
					} else {
						if($strWhere != ''){
							if($i > 0){
								if (strToUpper($arrSearch[$i - 1])=='AND' or strToUpper($arrSearch[$i - 1])=='OR' or strToUpper($arrSearch[$i - 1])=='NOT'){
									# Append nothing. Operator already used.
								} else {
									if($strWhere != '') $strWhere = $strWhere.' AND '; #ASSUME AND
								}	
							}
						}
						$strWhere = $strWhere."content LIKE '%".$arrSearch[$i]."%' ";
					}
			    }
			    $sql="SELECT * FROM news WHERE ".$strWhere." ORDER BY id";
				
			    $rsCat_query=mysql_query($sql); 
				#echo '<big>'.$sql.'</big><br>';
				#$rsCat_query=mysql_query($sql) OR die (mysql_error());
				
			    if (!(mysql_errno()==0)) {
				    echo '<big>There was a problem with the query syntax.</big><br>'; #log the error!
			    }
			    if (mysql_num_rows($rsCat_query)==0) {
				    echo '<big>No items were found matching the criteria in the news database.</big><br>';
				} else {
					echo 'Results from checking the news database:<br><br>';
				}
			    while($rsCat = mysql_fetch_array($rsCat_query)) {
					?><div id="author"><center><? echo $rsCat['subject']; ?></center></div><?
				    echo $rsCat['content'].'<br><br>';
			    }
				echo '<br><hr><br>';
			}
		
			$strWhere = ''; $sql = '';
			
			if($users){
			    for ($i=0; $i<count($arrSearch); $i++) {
					if (strToUpper($arrSearch[$i])=='AND' or strToUpper($arrSearch[$i])=='OR' or strToUpper($arrSearch[$i])=='NOT') {
						if (strToUpper($arrSearch[$i])=='NOT') {
							if($i > 0){
								if (strToUpper($arrSearch[$i - 1])=='AND' or strToUpper($arrSearch[$i - 1])=='OR' or strToUpper($arrSearch[$i - 1])=='NOT'){
									# Append nothing. Operator already used.
								} else {
									$strWhere = $strWhere.' AND '; #ASSUME AND
								}	
							}
							$i++;
							$strWhere = $strWhere." username != '".$arrSearch[$i]."'";
						} else {
							$strWhere = $strWhere." ".strToUpper($arrSearch[$i])." ";
						}
					} else {
						if (strToUpper($arrSearch[$i - 1])=='AND' or strToUpper($arrSearch[$i - 1])=='OR' or strToUpper($arrSearch[$i - 1])=='NOT'){
							# Append nothing. Operator already used.
						} else {
							if($strWhere != '') $strWhere = $strWhere.' OR '; #Assume OR
						}	
						$strWhere = $strWhere." username LIKE '".$arrSearch[$i]."'";
					}
			    }
			    $sql="SELECT * FROM users WHERE ".$strWhere." ORDER BY id";
				
			    $rsCat_query=mysql_query($sql); 
				#echo '<big>'.$sql.'</big><br>';
				#$rsCat_query=mysql_query($sql) OR die (mysql_error());
				
			    if (!(mysql_errno()==0)) {
				    echo '<big>There was a problem with the query syntax.</big><br>'; #log the error!
			    }
			    if (mysql_num_rows($rsCat_query)==0) {
				    echo '<big>No items were found matching the criteria in the user database.</big><br>';
				} else {
					echo 'The following users were found in the database:<br><br>';
				}
			    while($rsCat = mysql_fetch_array($rsCat_query)) {
				    echo $rsCat['username'].'<br>';
			    }
			}
			
			echo '<br><a href="/?op=search">Back</a><br>';
			?>
		</blockquote>
		</div></div>
		<?
	}
?>
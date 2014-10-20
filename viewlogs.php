<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------

	if(rank($userid) < 3) {
		blockhack();
	}

	if(rank($userid) < 4) $privatestring = "WHERE eventtype != 'CONFIDENTAL' ";
	if($_REQUEST['consolidate'] != ''){
		$privatestring .= " group by CONCAT(user,datetime,eventtype) ";
		$consolidate = '&consolidate=true';
	}
	
    $limit          = 25;               
    $query_count    = "SELECT * FROM logs $privatestring";
    $result_count   = mysql_query($query_count);
    $totalrows      = mysql_num_rows($result_count); 
	
	$page = sanitize($_REQUEST['page']);
	$orderby = sanitize($_REQUEST['orderby']);
	$mode = sanitize($_REQUEST['mode']);
	$id = sanitize($_REQUEST['id']);
	
	$specific = false;
	
	?>
		<div id="container">
		<div id="main2">
		<h2>View Logs</h2>
		<div align=center>
		<br>
	<?
	
	if($mode == 'delete'){
		if(rank($userid) == 5){
			$sql = "DELETE FROM logs WHERE id = $id";
			mysql_query($sql) or die("MySQL News Error (delete): ".mysql_error());

			WriteData($userid, 'msg', '<center><b><font color=lime>Log Entry Deleted.</font></b></center>');

			redirect("/?op=viewlogs&page=$page".$consolidate);
		} else {
			blockhack('no');
		}
	}
	
	if($id && !$mode){
		$limit = 1;
		$orderby = 'WHERE id='.mysql_real_escape_string($id);
		$specific = true;
		$query  = "SELECT * FROM logs WHERE id=".mysql_real_escape_string($id);
	}

    if($page < 1){
        $page = 1;
    }
	
    $limitvalue = $page * $limit - ($limit); 
	
    if(!$specific){
		if(!$orderby){
			$query  = "SELECT * FROM logs $privatestring ORDER BY id desc LIMIT $limitvalue, $limit";
		} else {
			$query  = "SELECT * FROM logs $privatestring ORDER BY $orderby desc LIMIT $limitvalue, $limit";
		}
	}
	
    $result = mysql_query($query) or die("Error: " . mysql_error()); 

    if(mysql_num_rows($result) == 0){
        echo("Nothing to Display!");
    }

    $bgcolor = "#333333"; // light gray

	#URLButton('Back To Console', '/?op=console');
		
    if(!$consolidate){
		?>[ &nbsp; <a href="/?op=viewlogs&consolidate=true" title="Merge Similar Events on a per user basis">Consolidate View</a> &nbsp; ]<br><br><?
	} else {
		?>[ &nbsp; <a href="/?op=viewlogs">Unconsolidate View</a> &nbsp; ]<br><br><?	
	}
	?>
	<table>
		<tr bgcolor=".<?=$bgcolor;?>.">
			<td>
				<center><font size=1><a href="/?op=viewlogs&page=<?=$page;?>&orderby=id<?=$consolidate;?>">Order By Date</a></font></center>
			</td>
			<td>
				<center><font size=1><a href="/?op=viewlogs&page=<?=$page;?>&orderby=user<?=$consolidate;?>">Order By Member Activity</a> | <a href="/?op=viewlogs&page=<?=$page;?>&orderby=eventtype<?=$consolidate;?>">Order By Event</a></font></center>
			</td>
	<?	
	#display this only if person is a coldr or higher
	if(rank($userid) > 2){
		echo '<td><center><font size=1><a href="/?op=viewlogs&page='.$page.'&orderby=ip'.$consolidate.'">Order By IP</a></font></center></td>';
	} else {
		echo "<td>&nbsp;</td>";
	}
	if(rank($userid) ==  5) echo "<td>&nbsp;</td>";
	echo "\n\t\t</tr>";
		
    while($row = mysql_fetch_array($result)){
        if ($bgcolor == "#333333"){
            $bgcolor = "#666666";
        }else{
            $bgcolor = "#333333";
        }

	    echo("<tr bgcolor=".$bgcolor."><td>");
	    echo('<font size=1>'.$row['datetime'].'</font>');
	    echo("</td><td>");
		
		if($consolidate){	# If it's turned on, instead of displaying detailed summaries for events, simply indicate event type.
			if($row['eventtype'] == 'CONFIDENTAL') $row['event'] = $row['event']; #do nothing
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
		}
		$lenlimit = 30;
		if(!$specific){
			$row['event'] = totalpurgehtml($row['event']);
			if(strlen($row['event']) > $lenlimit){
				$row['event'] = substr($row['event'], 0, $lenlimit).'... [ <a href="/?op=viewlogs&id='.$row['id'].$consolidate.'">more</a> ]';
			}
		}
		
		$initator = $row['user'];
		
		if(!$initator) $initator = 'Someone';
		
	    if($specific){
			$content = codify($row['event'], true);
			echo(ucfirst(whoisid($initator)).' '.$content);
		} else {
			echo(ucfirst(whoisid($initator)).' '.lcfirst($row['event']));
		}
	    echo "</td>";
		
		#display this only if person is a coldr or higher
		if(rank($userid) > 2){
			echo "<td><font size=1>".$row['ip']."</font></td>";
		} else {
			echo "<td><font size=1> IP SAVED </font></td>";
		}
		
		if(rank($userid) == 5) {
			if(!$consolidate){
				$delete = "/?op=viewlogs&page=$page&mode=delete&id=".$row['id'].$consolidate;
				echo '<td><a href="'.$delete.'" border=0 onclick="return confimdelete();"><img border=0 src="/images/icondelete.jpg"></a></td>';
			}
		}
		
		echo "</tr>";
    }

    echo("</table><br>");

	if(!$specific){
	
	    if($page != 1){ 
	        $pageprev = $page - 1;
	        
	        echo('<a href="/?op=viewlogs&page='.$pageprev.$consolidate.'"><< PREV</a> '); 
	    }else{
	    }

	    $numofpages = $totalrows / $limit; 
	    $expandlimit = 4;
		$minimum = $page - 5;
		if($minimum < 1) $minimum = 1;
	    for($i = $minimum; $i <= $page + $expandlimit; $i++){
	        if($i == $page){
	            echo($i." ");
	        }else{
	            echo('<a href="/?op=viewlogs&page='.$i.$consolidate.'">'.$i.'</a> ');
	        }
			if($i > $numofpages) break;
	    }
		
		if($i < $numofpages){
			echo ' ... ';
			
			$numofpages = floor($totalrows / $limit); 
		    $expandlimit = 3;
			$maximum = $numofpages - 5;
			if($page < 1) $minimum = 1;
		    for($i = $numofpages - $expandlimit; $i <= $numofpages; $i++){
		        if($i == $page){
		            echo($i." ");
		        }else{
		            echo('<a href="/?op=viewlogs&page='.$i.$consolidate.'">'.$i.'</a> ');
		        }
		    }
		}
		
	    if(($totalrows % $limit) != 0){
			if($i != ceil($numofpages)){
		        if($i == $page){
					echo($i." ");
		        }else{
		            echo('<a href="/?op=viewlogs&page='.$i.$consolidate.'">'.$i.'</a> ');
		        }
			}
	    }

	    if(($totalrows - ($limit * $page)) > 0){
	        $pagenext = $page + 1;
	        echo('<a href="/?op=viewlogs&page='.$pagenext.$consolidate.'">NEXT >></a>'); 
	    }else{
	    }
	} else {
		?><input type=button value="Go Back to Logs" id="abutton" onClick="history.back()"><?
	}

    mysql_free_result($result);
	echo '<br><br></div></div></div>';

?>

<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');

	# Begin Code
        # -------------

        global $sql_connection;

	$gid = $_GET['gid'];
	$lang = $_GET['lang'];
	if($lang == 'vb') $language = 'Visual Basic 6.0';
	if($lang == 'cpp') $language = 'C++';
	if($lang == 'java') $language = 'Java';
	if($lang == 'pas') $language = 'Pascal';
	if($lang == 'php') $language = 'PHP';
	if($lang == 'csharp') $language = 'C# (C Sharp)';

	if(!$language){
		error('Unable to generate code for the specified language. Teach me!');
		return;
	}

	if($gid == 'all'){
		$groupname = 'All';
	} else {
		$groupname = GetInfo('groups', 'id', $gid, 'groupname');
	}

	$orderby_all = 'GROUP BY messagename ORDER BY pgroup,messageid,direction DESC';
	$orderby_single = 'GROUP BY messagename ORDER BY messageid,direction DESC';
?>
<div id="container">
<div id="main2">
<h2>Code Generator</h2>
<div id="author"><center>Displaying <?=$groupname;?> Constants for <?=$language;?></center></div>
<br><center>
<pre id="code"><?
	if($lang == 'vb'){
		$commentizer = '\'';
		include 'bdif/codecomments.dm';
		if($gid != 'all'){
			$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' '.$orderby_single;
		} else {
			$sqlquery = 'SELECT * FROM packets '.$orderby_all;
		}
		$packetsarray = mysqli_query($sql_connection,$sqlquery);
		$pgroup = -1;
		while($row = mysqli_fetch_array($packetsarray)){
			if($row['pgroup'] != $pgroup){
				$pgroup = $row['pgroup'];
				$groupname = GetInfo('groups', 'id', $pgroup, 'groupname');
				if($ie) echo '&#13;&#10;';
				echo '<br>'.$commentizer.' '.$groupname.' Constants<br>';
				echo $commentizer.' -------------------<br><br>';
				if($ie) echo '&#13;&#10;';
			}
			$pid = $row['id'];
			$messageid = $row['messageid'];
			$messagename = $row['messagename'];

			echo 'CONST '.$messagename.'& = &H'.substr($messageid, 2).'<br>';
		}
	} elseif($lang == 'cpp'){
		$commentizer = '//';
		include 'bdif/codecomments.dm';
		if($gid != 'all'){
			$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' '.$orderby_single;
		} else {
			$sqlquery = 'SELECT * FROM packets '.$orderby_all;
		}
		$packetsarray = mysqli_query($sql_connection,$sqlquery);
		$pgroup = -1;
		while($row = mysqli_fetch_array($packetsarray)){
			if($row['pgroup'] != $pgroup){
				$pgroup = $row['pgroup'];
				$groupname = GetInfo('groups', 'id', $pgroup, 'groupname');
				if($ie) echo '&#13;&#10;';
				echo '<br>'.$commentizer.' '.$groupname.' Constants<br>';
				echo $commentizer.' -------------------<br><br>';
				if($ie) echo '&#13;&#10;';
			}
			$pid = $row['id'];
			$messageid = $row['messageid'];
			$messagename = $row['messagename'];

			echo '#define '.$messagename.' '.$messageid.'<br>';
		}
	} elseif($lang == 'java'){
		$commentizer = '//';
		include 'bdif/codecomments.dm';
		if($gid != 'all'){
			$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' '.$orderby_single;
		} else {
			$sqlquery = 'SELECT * FROM packets '.$orderby_all;
		}
		$packetsarray = mysqli_query($sql_connection,$sqlquery);
		$pgroup = -1;
		while($row = mysqli_fetch_array($packetsarray)){
			if($row['pgroup'] != $pgroup){
				$pgroup = $row['pgroup'];
				$groupname = GetInfo('groups', 'id', $pgroup, 'groupname');
				if($ie) echo '&#13;&#10;';
				echo '<br>'.$commentizer.' '.$groupname.' Constants<br>';
				echo $commentizer.' -------------------<br><br>';
				if($ie) echo '&#13;&#10;';
			}
			$pid = $row['id'];
			$messageid = $row['messageid'];
			$messagename = $row['messagename'];

			echo 'static final byte '.$messagename.' = '.$messageid.';<br>';
		}
	} elseif($lang == 'pas'){
		$commentizer = '//';
		include 'bdif/codecomments.dm';
		echo 'const<br>';
		if($gid != 'all'){
			$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' '.$orderby_single;
		} else {
			$sqlquery = 'SELECT * FROM packets '.$orderby_all;
		}
		$packetsarray = mysqli_query($sql_connection,$sqlquery);
		$pgroup = -1;
		while($row = mysqli_fetch_array($packetsarray)){
			if($row['pgroup'] != $pgroup){
				$pgroup = $row['pgroup'];
				$groupname = GetInfo('groups', 'id', $pgroup, 'groupname');
				if($ie) echo '&#13;&#10;';
				echo '<br>'.$commentizer.' '.$groupname.' Constants<br>';
				echo $commentizer.' -------------------<br><br>';
				if($ie) echo '&#13;&#10;';
			}
			$pid = $row['id'];
			$messageid = $row['messageid'];
			$messagename = $row['messagename'];

			echo '  '.$messagename.' = $'.substr($messageid, 2).';<br>';
		}
	} elseif($lang == 'csharp'){
		$commentizer = '//';
		include 'bdif/codecomments.dm';
		$commentizer = '&#09;//';
		echo 'public enum PacketIDConstants : byte<br>{<br>';
		if($gid != 'all'){
			$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' '.$orderby_single;
		} else {
			$sqlquery = 'SELECT * FROM packets '.$orderby_all;
		}
		$packetsarray = mysqli_query($sql_connection,$sqlquery);
		$pgroup = -1;
		ob_start();
		while($row = mysqli_fetch_array($packetsarray)){
			if($row['pgroup'] != $pgroup){
				$pgroup = $row['pgroup'];
				$groupname = GetInfo('groups', 'id', $pgroup, 'groupname');
				if($ie) echo '&#13;&#10;';
				echo '<br>'.$commentizer.' '.$groupname.' Constants<br>';
				echo $commentizer.' -------------------<br><br>';
				if($ie) echo '&#13;&#10;';
			}
			$pid = $row['id'];
			$messageid = $row['messageid'];
			$messagename = $row['messagename'];

			echo '&#09;'.$messagename.' = '.$messageid.',<br>';
		}
		$results = ob_get_contents();
		$results = substr($results, 0, strlen($results) - 5 );
		ob_end_clean();
		echo $results;
		echo '<br>}';
	} elseif($lang == 'php'){
		$commentizer = '#';
		include 'bdif/codecomments.dm';
		if($gid != 'all'){
			$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' '.$orderby_single;
		} else {
			$sqlquery = 'SELECT * FROM packets '.$orderby_all;
		}
		$packetsarray = mysqli_query($sql_connection,$sqlquery);
		$pgroup = -1;
		while($row = mysqli_fetch_array($packetsarray)){
			if($row['pgroup'] != $pgroup){
				$pgroup = $row['pgroup'];
				$groupname = GetInfo('groups', 'id', $pgroup, 'groupname');
				if($ie) echo '&#13;&#10;';
				echo '<br>'.$commentizer.' '.$groupname.' Constants<br>';
				echo $commentizer.' -------------------<br><br>';
				if($ie) echo '&#13;&#10;';
			}
			$pid = $row['id'];
			$messageid = $row['messageid'];
			$messagename = $row['messagename'];

			echo 'define(\''.$messagename.'\', '.$messageid.');<br>';
		}
	}
?></pre>
<br></center>
</div></div>

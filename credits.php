<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
?>
<div id="container">
<div id="main2">
<h2>BNETDocs Credits</h2>
<br><center>BNETDocs software written & maintained by <a href="http://www.doncullen.net/">Don Cullen</a> AKA <a href="http://www.dementedminds.net/index.php?op=profile&who=DM-Kyro">Kyro</a>.<br></center>
<br>
<center>Initial BNETDocs content compiled by <a href="mailto:arta@valhallalegends.com">Arta</a> and Skywing.<br></center>
<br>
<div id="author"><center>BNETDocs is administrated by:</center></div>
<br><center>
	<?
		$sqlquery = 'SELECT * FROM users WHERE levelofaccess > 3 ORDER BY id ASC';
		$membersarray = mysql_query($sqlquery);
		while($row = mysql_fetch_array($membersarray)){
			$username = $row['username'];
			echo $username.'<br>';
		} 
	?>
<br></center>
<div id="author"><center>BNETDocs is edited by:</center></div>
<br><center>
	<?
		$sqlquery = 'SELECT * FROM users WHERE levelofaccess=3 ORDER BY id ASC';
		$membersarray = mysql_query($sqlquery);
		while($row = mysql_fetch_array($membersarray)){
			$username = ucfirst($row['username']);
			echo $username.'<br>';
		} 
	?>
<br></center>
<div id="author"><center>Many thanks to the following, in no particular order, for their contributions:</center></div>
<br><center>
Arta, Skywing, Noodlez, Kane, Spht, Soul Taker, Barumonk[RC], iago[vL], TheMinistered,<br>
[vL]Kp, Cloaked, Lord[nK], Ringo, shadypalm88, UserLoser, LivedKrad, Joe[x86], R.a.b.b.i.t.,<br>
l2-Shadow, Smarter, Camel. While their contibutions aren't known to the new administrator (Kyro), their contributions are still nonetheless 
appreciated. If you're the contributor mentioned in the previous list, please contact Kyro to have your contributions listed here.<br><br> DevCode -- Programatically obtain the VerByte code without reliance on BNLS<br><br></center>
</div></div>
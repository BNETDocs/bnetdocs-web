<? global $ie; ?>
</td>
<div id="frills">
<td width=20% valign=top id="frills">
	<? if($ie){ ?>
	<div id="container">
	<div id="main2">
	<div id="author">ALERT</div>
	<table border=0 cellspacing=0 cellpadding=4><tr><td>
	We recommend you use Firefox to view this site. This site has been optimized for Firefox.<br>
	<br>
	<center><a href="http://getfirefox.com/" title="Get Firefox - The Browser, Reloaded.">
	<img src="http://www.mozilla.org/products/firefox/buttons/getfirefox_88x31.png"	width="88" height="31" border="0" alt="Get Firefox"></a></center>
	</td></tr></table>
	</div></div>
	<? } ?>

	<!--<div id="container">
	<div id="main2">
	<div id="author">BNLS Redirector Status</div>
		<div align=center>
		<table cellspacing=2 cellpadding=2 border=0>
		<?
			//$sid = GetInfo('servers', 'serveraddress', 'bnls.dementedminds.net', 'id');
			//$target = GetInfo('servers', 'id', $sid, 'target');
			//$sid = GetInfo('servers', 'serveraddress', $target, 'id');
			//$status = checkserver($sid);
			//echo '<tr><td><font size=2><br>bnls.dementedminds.net: </td><td><br><img src="'.statusimg($status).'"><br></font></td></tr>';
			//echo '<tr><td colspan=2><font size=2>Redirecting to: '.$target.'</td></tr>';
		?>
		</table>
		</div>
		<br>
	</div></div>-->
	
	<?
		$sqlquery = 'SELECT id,serveraddress,target,status FROM servers WHERE target != \'\'';
		$redirects = mysql_query($sqlquery); $displayed = false;
		while ($redirect = mysql_fetch_array($redirects)) {
			if (!$displayed) {
				echo "\t<div id=\"container\">\n\t<div id=\"main2\">\n\t<div id=\"author\">BNLS Redirecter Status</div>\n\t\t<div align=center>\n";
				$displayed = true;
			}
			echo "\t\t<table cellspacing=2 cellpadding=2 border=0>\n"
				."\t\t\t<tr><td><font size=2><br>" . $redirect['serveraddress'] . "</td><td><br><img src=\"".statusimg($redirect['status'])."\"><br></font></td></tr>\n"
				."\t\t\t<tr><td colspan=2><font size=2>Redirecting to: " . $redirect['target'] . "</td></tr>\n"
				."\t\t</table>\n";
		} if ($displayed) echo '</div></div>';
	?>
	
	<div id="container">
	<div id="main2">
	<div id="author">BNLS Server Status</div>
		<div align=center>
		<table cellspacing=2 cellpadding=2 border=0>
		<?
			$sqlquery = 'SELECT id,serveraddress,status FROM servers ORDER BY status DESC,id ASC';
			$serverarray = mysql_query($sqlquery);
			while($row = mysql_fetch_array($serverarray)){
				$sid = $row['id'];
				$saddress = $row['serveraddress'];
				$status = $row['status'];
				if($status=='offline'){
					$status = false;
				} else {
					$status = true;
				}
				/*if($saddress != 'bnls.dementedminds.net')*/ echo '<tr><td><font size=2>'.$saddress.': </td><td><img src="'.statusimg($status).'"><br></font></td></tr>';
			} 
		?>
		</table>
		</div>
		<br>
		<center><font size=1><img src="<? echo statusimg(true); ?>"> = Online &nbsp; &nbsp; &nbsp; <img src="<? echo statusimg(false); ?>"> = Offline<br><br></font></center>
	</div></div>
	
	<div id="container">
	<div id="main2">
	<div id="author">Battle.net Server Status</div>
		<div align=center>
		<?
			$sqlquery = 'SELECT version FROM bnetservers GROUP BY version ASC';
			$versions = mysql_query($sqlquery);
			while ($version = mysql_fetch_array($versions)) {
				echo '<h2>' . $version['version'] . '</h2><table cellspacing=2 cellpadding=2 border=0>';
				$sqlquery = 'SELECT id,serveraddress,status FROM bnetservers WHERE version = \'' . $version['version'] . '\' ORDER BY status DESC,id ASC';
				$serverarray = mysql_query($sqlquery);
				while($row = mysql_fetch_array($serverarray)){
					$sid = $row['id'];
					$saddress = $row['serveraddress'];
					$status = $row['status'];
					if($status=='offline'){
						$status = false;
					} else {
						$status = true;
					}
					echo '<tr><td><font size=2>'.$saddress.': </td><td><img src="'.statusimg($status).'"><br></font></td></tr>';
				}
				echo '</table>';
			}
		?>
		</div>
		<br>
		<center><font size=1><img src="<? echo statusimg(true); ?>"> = Online &nbsp; &nbsp; &nbsp; <img src="<? echo statusimg(false); ?>"> = Offline<br><br></font></center>
	</div></div>
	
	<div id="links">
	<div id="container">
	<div id="main2">
	<div id="author">Links</div>
		<center>
		<a href="http://forum.valhallalegends.com/" target="_blank"><img src="/images/vl_linkto.gif" alt="Valhalla Legends" title="vL Forums"></a>
		<a href="http://www.gosugamers.net" target="_blank"><img src="/images/GG_linktous_91x32_1.gif" alt="Gosu Gamers"></a><br>
		<a href="http://www.jailout2000.com" target="_blank"><img src="/images/Jailout2000.jpg" alt="Jailout2000" title="Jailout2000's Website"></a>
		<a href="http://www.darkblizz.org" target="_blank"><img src="/images/DarkBlizzbnetweb.gif" alt="DarkBlizz" title="DarkBlizz - Making Battle.net Magic"></a><br>
		</center>
	</div></div></div>
	
	<div id="rssfeeds">
	<div id="container">
	<div id="main2">
	<div id="author">RSS Feeds</div>
		<br>
		<a href="//www.bnetdocs.org/newsrss.php"><img src="/images/rss.gif"> &nbsp; BNETDocs News</a><br>
		<?
			if($userid){
				?>
				<a href="//www.bnetdocs.org/logsrss.php"><img src="/images/rss.gif"> &nbsp; BNETDocs Logs</a><br>
				<?
			}
		?>
		<br>
	</div></div></div>
	</td>
</tr></table>
<br><br>
<div id="copyrights">
<div id="author">Copyrights</div>
<br>Site scripts and design copyrights reserved to <a href="http://www.doncullen.net">Don Cullen</a>.<br>
Contents copyrighted to Blizzard and their parent corporation, Vivendi.<br>
Main credits for contents goes to Arta. <a href="/?op=credits">View the rest of credits.</a><br>
Demented Minds copyrights reserved to <a href="http://www.doncullen.net">Don Cullen</a> 2003-present.<br>
Copyright infringements will be prosecuted to the fullest extent allowable by law.
<br>
<a href="/?op=legalism"><font size="1">Please view our legal disclaimer and terms of service.</font></a><br><br>
</div>
</div>
<? if( extension_loaded('newrelic') ) { echo newrelic_get_browser_timing_footer(); } ?>
</body>
</html>

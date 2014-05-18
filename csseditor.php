<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	if(!$userid) die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	if(!$_POST){
		if($_GET['defaultcss']) WriteData($userid, 'usercss', 'default');
		if($_GET['lowfi']) WriteData($userid, 'usercss', 'lowfi');
		if($_GET['dark']) WriteData($userid, 'usercss', 'dark');
		if($_GET['dark'] || $_GET['lowfi'] || $_GET['defaultcss']){
			redirect('/?op=csseditor');
			exit();
		}
		$usercss = GetData($userid, 'usercss');
		if($usercss == 'custom'){
			$csstype='<span id="important">Custom</span>';
		} elseif(substr($usercss, 0, 6) == 'user: ') { #using another member's css file
			$csstype='Subscribed to '.substr($usercss, 6).'\'s CSS Theme.';
		} else {
			$csstype=$usercss;
		}
		
		$found = false;
		
		if($usercss == 'custom'){ #user's own css file
			$usercssfile = 'usercss/'.whoisid($userid).'.css';
			$csstype='<span id="important">Custom</span>';
			$found = true;
		} elseif(substr($usercss, 0, 6) == 'user: ') { #using another member's css file
			$usercssfile = 'usercss/'.substr($usercss, 6).'.css';
			$csstype='Subscribed to '.substr($usercss, 6).'\'s CSS Theme.';
			$found = true;
		} else {
			#do loop, if found, break, otherwise, use v1 styling
			$sqlquery = 'SELECT * FROM themes';
			$themearray = mysql_query($sqlquery);
			while($row = mysql_fetch_array($themearray)){
				$themename = $row['theme'];
				$cssfile = $row['cssfile'];
				if($cssfile == $usercss){
					$usercssfile = 'usercss/'.$cssfile.'.css';
					$csstype=$usercss;
					$found = true;
				}
			}
			if(!$csstype) $csstype='<span id="important">Custom using v1.0 CSS Theme Editor</span>';
		}
		
		if($found){
			$usercss = file_get_contents($usercssfile);
		}
		
		?><div id="container">
		<div id="main2">
		<h2>CSS Theme Editor</h2>
		<div align=center>
		<br>
		<center>Warning: if you modify the CSS, it becomes custom!<br><br>
		If you were using a pre-set theme, and you switch to custom, your CSS will *not* auto-update itself if the contributor of the pre-set CSS Theme updates it!</center><br>
		<? echo '<center>CSS Theme in use: '.$csstype.'</center><br>'; ?>
		<form method="POST" name="newcss" action="/?op=csseditor">
		<textarea name="csstheme" style="width: 90%;" id="inputbox" rows="20" cols="81"><?=$usercss;?></textarea>
		<br><br><input type="submit" id="abutton" style="width:900" value="Save Custom"></form><br><a href="/?op=csseditor&dark=true">Load Dark Redux CSS</a>
		<br><a href="/?op=csseditor&defaultcss=true">Load Default BNETDocs CSS</a>
		<br><a href="/?op=csseditor&lowfi=true">Load Low-fi CSS</a>
		<br><br>
		</div></div></div><?
	} else {
		#Process post
		$csstheme = $_POST['csstheme'];
		$usercssfile = 'usercss/'.whoisid($userid).'.css';
		if (is_writable($usercssfile))
		{
			$fp = fopen($usercssfile, 'w');
			fwrite($fp, $csstheme);
			fclose($fp);
			WriteData($userid, 'usercss', 'custom');
		} else {
			logthis($userid, 'was unable to save their custom CSS file.', 'CONFIDENTAL');
		}
		redirect(curPageURL());
	}
?>
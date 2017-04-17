<? global $ip, $userid, $auth; $openedmenus = $_GET['ep'];

  // Fight For The Future - Campaign Enables
  $FFTF_DATE            = date('Y-m-d');
  $FFTF_STOPTHESLOWLANE = ($FFTF_DATE == '2014-05-15');
  $FFTF_RESETTHENET     = ($FFTF_DATE == '2014-06-05');
  $FFTF_BATTLEFORTHENET = ($FFTF_DATE == '2014-09-10');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	<meta name="description" content="Battle.Net Logon Sequences, Packets, information and protocols reference site.">
	<meta name="keywords" content="Battle.Net, Logon Sequences, Packets, information, protocols reference, programming, coding, Starcraft, Warcraft, Diablo">

	<?
        if( extension_loaded('newrelic') ) { echo newrelic_get_browser_timing_header(); }
	if(preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT'])) {
		$ie = true;
	} else {
		$ie = false;
	}
	if($userid){
		$usercss = GetData($userid, 'usercss');
		if($usercss){
			$found = false;
			if($usercss == 'custom'){ #user's own css file
				$usercssfile = '/usercss/'.whoisid($userid).'.css';
				?><link rel="stylesheet" type="text/css" href="<?=$usercssfile;?>"><?
			} elseif(substr($usercss, 0, 6) == 'user: ') { #using another member's css file
				$usercssfile = 'usercss/'.substr($usercss, 6).'.css';
				?><link rel="stylesheet" type="text/css" href="<?=$usercssfile;?>"><?
			} else {
				#do loop, if found, break, otherwise, use v1 styling
				$sqlquery = 'SELECT * FROM themes';
				$themearray = mysql_query($sqlquery);
				while($row = mysql_fetch_array($themearray)){
					$themename = $row['theme'];
					$cssfile = $row['cssfile'];
					if($cssfile == $usercss){
						$usercssfile = 'usercss/'.$cssfile.'.css';
						$found = true;
					}
				}
				if(!$found){
					?>
					<!-- Now include user's css if available -->
					<style type="text/css">
					<?=$usercss;?>
					</style>
					<!-- End User CSS Inclusion -->
					<?
				} else {
					?><link rel="stylesheet" type="text/css" href="<?=$usercssfile;?>"><?
				}
			}
		} else {
			$usercssfile = '/usercss/default.css';
			?><link rel="stylesheet" type="text/css" href="<?=$usercssfile;?>"><?
		}
	} else {
		$usercssfile = '/usercss/default.css';
		?><link rel="stylesheet" type="text/css" href="<?=$usercssfile;?>"><?
	}
	?>
	<title>BNETDocs: Redux</title>
	<script src="/js/standard.js" type="text/javascript"></script>
	<link rel="alternate" type="application/rss+xml" title="BNETDocs Redux News" href="/newsrss.php">
	<link rel="icon" type="image/png" href="/favicon.png" sizes="32x32">
  <?
  // FIGHT FOR THE FUTURE - STOP THE SLOW LANE (http://www.stoptheslowlane.com/)
  if ($FFTF_STOPTHESLOWLANE) {
    ?>
	<style id="_sl_hide" type="text/css">body { display: none; }</style>
    <?
    if (rand(0, 1) == 1) {
      ?>
	<script type="text/javascript">var _sl_options = { animation: 'blur' }; // Alternate Comcast animation</script>
      <?
    }
    ?>
	<script src="//fightforthefuture.github.io/stoptheslowlane/widget/slowlane.js"></script>
    <?
  } // END OF STOP THE SLOW LANE
  // FIGHT FOR THE FUTURE - BATTLE FOR THE NET (https://www.battleforthenet.com/)
  if ($FFTF_BATTLEFORTHENET) {
    ?>
        <script src="//fightforthefuture.github.io/battleforthenet-widget/widget.min.js" async></script>
    <?
  }
  ?>
</head>
<body onload="doonload()">
<?
  // FIGHT FOR THE FUTURE - RESET THE NET (https://www.resetthenet.org/)
  if ($FFTF_RESETTHENET) {
    ?>
  <script type="text/javascript">
    window._idl = {};
    _idl.variant = "modal";
    _idl.campaign = "reset-the-net";
    (function() {
      var idl = document.createElement('script');
      idl.type = 'text/javascript';
      idl.async = true;
      idl.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'members.internetdefenseleague.org/include/?url= <http://members.internetdefenseleague.org/include/?url=>' + (_idl.url || '') + '&campaign=' + (_idl.campaign || '') + '&variant=' + (_idl.variant || 'modal');
      document.getElementsByTagName('body')[0].appendChild(idl);
    })();
  </script>
  <?
  } // END OF RESET THE NET
?>
<div id="content" align=center>
<div align=left id="logo"><img src="/images/bnetdocslogo.png"><br>
<br></div>
<div align=left id="textonlylogo"><br><h1>BNETDocs Redux</h1></div>
<table border=0 cellspacing=0 cellpadding=7 width=100%><tr>
<td width=20% valign=top nowrap>
	<div id="container">
	<div id="main2">
	<div align=left>
	<h2>Navigation</h2>
	<div id="divMain_Menu"><div id="author">Main Menu</div><div class="navmenu" id="bodyMain_Menu">
		<a href="/">Main Page</a><br>
		<a href="https://www.bnetdocs.org/">BNETDocs: Phoenix</a><br>
		<a href="/?mode=all">View News History</a><br>
		<a href="generatedocs.php">Download BNETDocs as Text</a><br>
		<a href="/?op=search">Search BNETDocs</a> &nbsp; <font color=red size=1>Incomplete</font><br>
		<a href="/?op=credits">Credits</a><br>
		<a href="/archives/" target="_blank">BNETDocs Archives</a><br><br>
        </div></div>
<?php if (!$loginDisabled) { ?>
	<div id="divConsole"><div id="author">Console</div><div class="navmenu" id="bodyConsole">
		<?
			if($userid){
				#session exists
				include 'bdif/console.dm';
			} else {
				#no session, show login
				#echo '<div id="login" onclick="ajax(\'login.php\', \'?op=login\', \'login\');">Click to login.</div>';
				include 'bdif/login.dm';
			}
		?>
        </div></div>
<?php } ?>
	<div id="divDocuments"><div id="author">Documents</div><div class="navmenu" id="bodyDocuments">
		<?
		$sqlqueryz = 'SELECT * FROM documents ORDER BY title ASC';
                global $sql_connection;
		$docarray = mysqli_query($sql_connection,$sqlqueryz);
		$doccount = 0;
		function _clearance_name($clearance)
		{
			switch ($clearance)
			{
				case 0: $clearancename = 'Guest'; break;
				case 1: $clearancename = 'User'; break;
				case 2: $clearancename = 'Superuser'; break;
				case 3: $clearancename = 'Editor'; break;
				case 4:
				case 5: $clearancename = 'Administrator'; break;
				default:
					$clearancename = 'Unknown&nbsp;('.$clearance.')';
			}
			return $clearancename;
		}
		while($rowz = mysqli_fetch_array($docarray)){
			$did = $rowz['id'];
			$title = delslash($rowz['title']);
			$clearance = $rowz['clearance'];
			if($clearance <= rank($userid)){
				echo '<a href="/?op=doc&did='.$did.'">'.$title.'</a>'.(rank($userid) >= 4 ? '<span>'._clearance_name($clearance).'</span>' : '').'<br>'."\n";
			}
			$doccount++;
		}
		if($doccount < 1) echo 'No documents available.';
		$title = '';
		?>
	</div></div>
	<div id="divGenerate_Code"><div id="author" nowrap>Generate Code: All packets</div><div class="navmenu" id="bodyGenerate_Code">
		<a href="/?op=generatecode&gid=all&lang=vb">Visual Basic 6.0</a><br>
		<a href="/?op=generatecode&gid=all&lang=cpp">C++</a><br>
		<a href="/?op=generatecode&gid=all&lang=csharp">C#.NET (C Sharp)</a><br>
		<a href="/?op=generatecode&gid=all&lang=java">Java</a><br>
		<a href="/?op=generatecode&gid=all&lang=pas">Pascal</a><br>
		<a href="/?op=generatecode&gid=all&lang=php">PHP</a><br><br>
	</div></div>
	<div id="divPackets"><div id="author">Packets</div><div class="navmenu" id="bodyPackets">
		Quick Jump: [ <a href="/?op=help&topic=quick jump">help</a> ]<br><font size=1><br></font>
		<form method="POST" name="quickjumpform" action="/?op=packet">
		<input type="text" id="inputbox" name="quickjump"><input type="submit" id="abutton" value="go">
		</form>
		<br>
<?
                $sqlquery_traffic = mysqli_query($sql_connection,'SELECT * FROM traffic ORDER BY id');
                $traffic = array();
                while($sqlresult_traffic = mysqli_fetch_array($sqlquery_traffic)) {
                        $traffic[$sqlresult_traffic["id"]] = $sqlresult_traffic;
                }
		$sqlquerya = 'SELECT * FROM groups ORDER BY displayorder';
		$groupsarray = mysqli_query($sql_connection,$sqlquerya);
		$zs1 = 1;
		while($rowa = mysqli_fetch_array($groupsarray)){
			$gid = $rowa['id'];
			$groupname = $rowa['groupname'];
			$groupdivname = str_replace(' ', '_', $groupname);
			$groupdivname = str_replace('.', '_', $groupdivname);
			echo '<div id="'.$groupdivname.'">'."\n".$groupname.' [ <a id="x'.$zs1.'1" href="#" onclick="toggle_visibility(\'a'.$zs1.'\'); toggle_name(this); return(false);">+'.$clicktext.'</a> ]<br>'."\n".'<div class="togglable" id="a'.$zs1.'">'."\n";
			$zs1++;
			if(($groupname == 'D2GS Messages') || ($groupname == 'W3GS Messages')){
				$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' ORDER BY direction,messageid,messagename ASC';
			} else {
				$sqlquery = 'SELECT * FROM packets WHERE pgroup='.$gid.' ORDER BY messageid,direction DESC';
			}
			$packetsarray = mysqli_query($sql_connection,$sqlquery);
			while($row = mysqli_fetch_array($packetsarray)){
				$pid = $row['id'];
                                //$direction = $row['direction'];
                                $direction = $traffic[$row['direction']]['shortdescr'] . ' ';
				//$direction = GetInfo('traffic', 'id', $direction, 'shortdescr') . ' ';
				$messageid = '['.$row['messageid'].'] ';
				$messagename = $row['messagename'];
				$pclearance = $row['clearance'];
				switch ($row['status']) {
					case 0:
						$status = '<font class="raw">RAW</font>'."\n";
						break;
					case 1:
						$status = '';
						break;
					case 2:
						$status = '<font class="research">RESEARCH</font>'."\n";
						break;
					case 3:
						$status = '<font class="defunct">DEFUNCT</font>'."\n";
						break;
				}
				if($pclearance > 0){
					if($userid){
						if(rank($userid) > $pclearance){
							echo '<a href="/?op=packet&pid='.$pid.'"><font size=1>'.$direction.$messageid.$messagename.'</font></a>'.$status.'<br>'."\n";
						}
						if(rank($userid) == $pclearance){
							echo '<a href="/?op=packet&pid='.$pid.'"><font size=1>'.$direction.$messageid.$messagename.'</font></a>'.$status.'<br>'."\n";
						}
					}
				} else {
					if(!$pclearance){
						echo '<a href="/?op=packet&pid='.$pid.'"><font size=1>'.$direction.$messageid.$messagename.'</font></a>'.$status.'<br>'."\n";
					}
				}
			}
			echo "\n".'<br>'."\n".'<font id="viewcode">View Code: [&nbsp; <a href="/?op=generatecode&gid='.$gid.'&lang=pas">PAS</a> <a href="/?op=generatecode&gid='.$gid.'&lang=cpp">CPP</a> <a href="/?op=generatecode&gid='.$gid.'&lang=vb">VB</a> <a href="/?op=generatecode&gid='.$gid.'&lang=php">PHP</a> &nbsp;]</font><br>'."\n".'</div>'.'<br>'."\n".'</div>'."\n";
		} 
		?>
		</div></div>
	<div id="divOther_Resources"><div id="author">Other Resources</div><div class="navmenu" id="bodyOther_Resouces">
		<a href="/archives/bnlsprotocolspec.txt" target="_blank">BNLS Protocol Specs</a><br>
		<a href="/archives/bnsp-latest.txt" target="_blank">BotNet Protocol Specs</a><br>
		<a href="http://forum.valhallalegends.com/index.php?board=17.0" target="_blank">BNET Bot Development Forums</a><br><br>
	</div></div></div></div></div>
</td>
<td width=60% valign=top>
<?
  // FIGHT FOR THE FUTURE - RESET THE NET (https://www.resetthenet.org/)
  if ($FFTF_RESETTHENET) {
?>
<a href="https://www.resetthenet.org/" target="_blank" style="display:inline-block;width:100%;"><img src="https://bnetdocs.org/images/resetthenet.png" alt="Reset The Net" title="Reset The Net" style="border:0px;padding-top:10px;width:100%;" /></a>
<?
  }
?>

<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	if(!$userid) die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	if (!empty($_POST['text'])){
	   
	   echo '<link rel="stylesheet" href="hilight.css" />';
	   echo '<div style="border: solid 1px orange; padding: 20px; margin: 20px">';
	   
	   require_once '/usr/share/pear/Text/';
	   $highlighter = Text_Highlighter::factory('php');
	   echo $highlighter->highlight($_POST['text']);
	   
	   echo '</div>';
	}
?>

<form action="/?op=experiment" method="post">
   <textarea name="text" style="width: 300px; height: 200px"><?php echo @$_POST['text']; ?></textarea>
   <br />
   <input type="submit" />
</form>
<?
	# 

	# Block Direct Access Attempts
	# -------------------------------
	
		global $auth, $ie, $userid;
		if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		if(rank($userid) < 1) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
		$email = GetData($userid, 'email');
		if(!FieldVerify('id', 'options', $userid)){	# Check to see if user has no settings (if the user is new to BNETDocs, usually doesn't)
			# Doesn't have settings, create defaults
			mysql_query("INSERT INTO `options` (`id`) VALUES ($userid)") or die("CreateSettings Error: ".mysql_error());
		}
		
		if($_POST){
			if($email != $_POST['email']){	# Change of email
				WriteData($userid, 'email', $_POST['email']);
			}
			if($_POST['newpassword']){		# Change of password
				WriteData($userid, 'password', md5($_POST['newpassword']));
			}
			/* WriteSettings($userid, 'showbrs', $_POST['showbrs']);     # Write the rest of settings even if there was no change
			WriteSettings($userid, 'showbss', $_POST['showbss']);
			WriteSettings($userid, 'showbnss', $_POST['showbnss']); */
			WriteSettings($userid, 'hidesidebar', $_POST['hidesidebar']);
			WriteSettings($userid, 'qjautosuggest', $_POST['qjautosuggest']);
			WriteSettings($userid, 'showrss', $_POST['showrss']);
			WriteSettings($userid, 'hidefooter', $_POST['hidefooter']);
			WriteSettings($userid, 'theme', $_POST['theme']);
			WriteSettings($userid, 'jsenabled', $_POST['javascriptok']);
			WriteData($userid, 'msg', 'Settings Saved!');
			unset($_POST);
			return;
		}
		
		/* if(GetOption($userid, 'showbrs') == 1) $brs = 'checked';		
		if(GetOption($userid, 'showbss') == 1) $bss = 'checked';
		if(GetOption($userid, 'showbnss') == 1) $bnss = 'checked'; */
		if(GetOption($userid, 'hidesidebar') == 1) $hidesidebar = 'checked';
		if(GetOption($userid, 'qjautosuggest') == 1) $qjas = 'checked';
		if(GetOption($userid, 'showrss') == 1) $rss = 'checked';
		if(GetOption($userid, 'hidefooter') == 1) $hidefooter = 'checked';
		if(GetOption($userid, 'jsenabled') == 1) $javascriptok = 'checked';
		if(GetOption($userid, 'theme') == 0){
			$defaulttheme = 'checked';
		} else {
			$customtheme = 'checked';
		}

		ob_start();
		?>
		<h1 class="inline">Options</h1>&nbsp;&nbsp;&nbsp;&nbsp;<br /><br />
		<form name="options" action="/?op=options" method="POST">
		<table border="0px" width="98%" height="100%" cellspacing="0px" cellpadding="10px">
			<tr>
				<td valign="top">
					<fieldset class="smallpanel">
						<legend>Account Information</legend>
						<font class="notice">If you don't want to change your password, leave the password field blank.</font><br /><br />
						<label>Email:</label><br />
						<input value="<?=$email;?>" name="email" type="textbox"><br />
						<br />
						<label>Change Password:</label><br />
						<input name="newpassword" type="textbox"><br /><br />
					</fieldset>
					<fieldset class="smallpanel">
						<legend>Sidebar Options</legend>
						<font class="notice">Uncheck what you do not want displayed on your sidebar.</font><br /><br />
						<!-- Jailout2000: Added "id" to input-checkbox-tags and added "for" attribute to label-tags -->
						<!-- <input value="1" name="showbrs" id="showbrs" type="checkbox" <?=$brs;?>><label for="showbrs">BNLS Redirector Status</label><br />
						<input value="1" name="showbss" id="showbss" type="checkbox" <?=$bss;?>><label for="showbss">BNLS Server Status</label><br />
						<input value="1" name="showbnss" id="showbnss" type="checkbox" <?=$bnss;?>><label for="showbnss">Battle.net Server Status</label><br /> -->
						<input value="1" name="showrss" id="showrss" type="checkbox" <?=$rss;?>><label for="showrss">RSS Feeds</label><br />
						<br />
						<input value="1" name="hidesidebar" id="hidesidebar" type="checkbox" <?=$hidesidebar;?>><label for="hidesidebar">Remove entire sidebar</label><br />
					</fieldset>
					<fieldset class="smallpanel">
						<legend>Theme Manager</legend>
						<label>Choose a theme:</label><br />
						<!-- Jailout2000: Added "id" to input-radio-tags and added "for" attribute to label-tags -->
						<input type="radio" name="theme" id="theme1" value="0" <?=$defaulttheme;?>><label for="theme1">Default</label><br />
						<input type="radio" name="theme" id="theme2" value="1" <?=$customtheme;?>><label for="theme2">Custom</label><br /><br />
						<font class="notice">If you'd like to use your own custom theme, select custom, then click 'Save Options'. It will then take you to the CSS Theme editor. You also can access it via the console (after selecting custom).</font>
					</fieldset>
					<fieldset class="smallpanel">
						<legend>Other Features</legend>
						<font class="notice">If you see JS next to an option, it requires Javascript to be enabled.</font><br /><br />
						<!--<input value="1" name="qjautosuggest" type="checkbox" <?=$qjas;?>><label>Enable QuickJump Suggest</label> <font class="required">JS</font><br />-->
						<input value="1" name="javascriptok" id="javascriptok" type="checkbox" <?=$javascriptok;?>><label for="javascriptok">Allow Javascript</label> <font class="required">JS</font><br /><br />
						<!-- Jailout2000: Added "id" to the input-checkbox-tag and added "for" attribute to the label-tag -->
						<input value="1" name="hidefooter" id="hidefooter" type="checkbox" <?=$hidefooter;?>><label for="hidefooter">Hide copyright footer</label> <font class="required">*</font><br /><br />
						<font class="required">*Hiding the footer does not constitue as a nullification or release of copyrights and terms.</font><br /><br />
					</fieldset>
				</td>
				<td width="150px" valign="top" align="center">
					<fieldset class="thinpanel">
						<legend>Actions</legend>
						<font class="notice">You need to click 'Save Options' in order for changes to take effect.</font><br /><br />
						<input class="optionbutton" type="submit"  name="save" value="Save Options" /><br /><br />
					</fieldset>
				</td>
			</tr>
		</table>
		<input type="hidden" name="operation" value="savesettings" />
		</form>
		<?

	# End Code
	# -------------		
		$out = ob_get_clean();
		DisplayDialogBox('' , $out);
?>
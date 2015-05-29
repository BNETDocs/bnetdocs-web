<?
	// Jailout2000: I have edited this file, I have not marked where at.
	//
	$xmlexample = nl2br(htmlentities ('   <format>
      <field type="DWORD" name="dwGameCount" descr="Number of Games" />
      <field type="DWORD" case="dwGameCount == 0" descr="Status" />
      <repeat case="dwGameCount > 0">
         <field type="WORD" descr="Address Family" default="AF_INET" />
         <field type="WORD" descr="Port" />
         <field type="DWORD" descr="Elapsed time (in seconds)" />
         <field type="STRING" descr="Game Name" />
      </repeat>
   </format>'));
?>



<div class="domtabcontainer">

<div class="domtab">
	 <a name="top" id="top"></a>
  <ul class="domtabs">
	<li><a href="#t1">Edit Attributes</a></li>
	<li><a href="#t2">Edit Format</a></li>
	<li><a href="#t3">Edit Remarks</a></li>
    <li><a href="#t4">Old Document</a></li>
  </ul>
  <form method="POST" name="packeteditor" action="/?op=pacman&action=<?=$action;?>">
  <input type="hidden" name="id" value="<?=$id?>">
  <table class="tabboxtable" border=0 cellspacing=0 cellpadding=0>
  <tr>
  <td>
  <div class="tabbox">
    <a name="t1" id="t1"></a>
	<br />
	<table border=0 cellspacing=2 cellpadding=2 width=100%>
		<tr>
			<td width=0>Packet ID:</td>
			<td width=100%>
				<input type="text" name="packetid" value="<?=$msgid;?>" size=45 id="inputbox">
				<? HelpLink('Packet id', 'Packet ID<br /><br />This is the identifer of the packet. Every packet has a unique identifier number. Identifers are a single byte that goes from 0 to 255.<br /><br />Usually these identifers are displayed as hexadecimal, that is, prefixed with a "0x" then the hexadecimal value of the identifer.'); ?>
				&nbsp;  &nbsp;  &nbsp; <font class="notice">Example: 0x00</font>
			</td>
		</tr>
		<tr>
			<td nowrap>Packet Name:</td>
			<td>
				<input type="text" name="name" value="<?=$msgname;?>" size=45 id="inputbox">
				<? HelpLink('Packet name', 'Packet Name<br /><br />This is the name of the packet. The name is separated by an underscore, the left side of the name is the classifier, the right side of the name is the actual name. Example: SID_NULL. In this case, SID would classify it as a Battle.net TCP packet, and NULL would tell us the name of the packet.<br /><br />Available Classifiers: <br /><br />SID = Battle.Net packets<br />SCUDP = StarCraft UDP packets<br />BNLS = Battle.Net Login System packets<br />MCP = Realm packets<br />D2GS = Diablo 2 Game Server packets<br />PKT = Battle.Net UDP packets<br />PACKET = BotNet packets<br />W3GS = WarCraft 3 Game Server packets'); ?>
				&nbsp;  &nbsp;  &nbsp; <font class="notice">Example: SID_NULL</font>
			</td>
		</tr>
		<tr>

			<td>Direction:</td>
			<td>
				<select name="direction" size="1">
					<option value="1"<? if($direction == 1) echo ' selected'; ?>>Server to Client</option>
					<option value="0"<? if($direction == 0) echo ' selected'; ?>>Client to Server</option>
					<option value="2"<? if($direction == 2) echo ' selected'; ?>>Client to Client</option>
				</select> 
				<? HelpLink('Packet direction', 'Packet Direction<br /><br />This is the direction in network traffic the packet goes through when being sent.<br /><br />Server to Client: Data sent from the server to your client.<br />Client to Server: Data sent from your client to the server.<br />Client to Client: Data from one client to another client.'); ?>
			</td>
		</tr>
		<tr>

			<td>Protocol:</td>
			<td>
				<select name="protocol" size="1">
					<option value="0"<? if($protocol == 0) echo ' selected'; ?>>TCP</option>
					<option value="1"<? if($protocol == 1) echo ' selected'; ?>>UDP</option>
				</select> 
				<? HelpLink('Packet protocol', 'Packet protocol<br /><br />This is the protocol used for the packet.<br /><br />Available protocols: TCP, UDP'); ?>
			</td>
		</tr>
		<tr>
			<td valign=top><br />Used By:</td><td>
				<?
					$SQL = 'SELECT * FROM `games` ORDER BY `id` ASC';
					$gamearray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
					$i = 0;
					while($row = mysql_fetch_array($gamearray)){
						$gameid = $row['id'];
						$gamename = $row['gamename'];
						$gameimage = $row['imagepath'];
						// Jailout2000: Begin edits.
						// echo '<input name="g'.$gameid.'"  value="1" type="checkbox"><img src="'.$gameimage.'" alt="'.$gamename.'">&nbsp;&nbsp;&nbsp;&nbsp';
						if ($gameid==$msgusedby) { $check = " checked=checked"; } else { $check = ""; }
						echo '<input name="g'.$gameid.'"  id="g'.$gameid.'"'.$check.' title="'.$gamename.'" type="checkbox"><label for="g'.$gameid.'"><img src="'.$gameimage.'" alt="'.$gamename.'" title="'.$gamename.'"></label>&nbsp;&nbsp;&nbsp;&nbsp';
						// Jailout2000: End edits.
						$i++;
						if($i == 4){
							echo '<br />';
							$i = 0;
						}
					}
					if($i != 0) echo '<br />';
					// Jailout2000: Begin edits.
					if (empty($check)) { $check = " checked=checked"; } else { $check = ""; }
					echo '<input name="none" id="none"'.$check.' type="checkbox"><label for="none">None</label><br>';
					// Original line after this PHP segment: <input name="none"  value="1" type="checkbox">None<br>
					// Jailout2000: End edits.
				?>
			</td>
		</tr>
		<tr>
			<td nowrap><br />Packet Status:</td>
			<td>
				<br />
				<select name="status" size="1">
					<option value="0"<? if($direction == 0) echo ' selected'; ?>>XML Needed</option>
					<option value="1"<? if($direction == 1) echo ' selected'; ?>>Normal</option>
					<option value="2"<? if($direction == 2) echo ' selected'; ?>>Research</option>
					<option value="3"<? if($direction == 3) echo ' selected'; ?>>Defunct</option>
				</select>
				<? HelpLink('Packet Documentation', 'Packet Status<br /><br />
																		XML Needed: XML formatting incomplete, edit only mode<br />
																		Normal: Completely researched, active packet<br />
																		Research: Research still under progress, active packet<br />
																		Defunct: Packet no longer used/available'); ?>
			</td>

		</tr>
		<tr>
			<td colspan="2">
				<br />
				Clearance Required: &nbsp; 
				<? HelpLink('Packet Documentation', 'Packet Clearance<br /><br />
																		Guest: Can be viewed by anyone (<b><i>recommended</i></b>)<br />
																		Member: Can be viewed only by logged in BNETDocs users<br />
																		Patron: Can be viewed only by patrons or higher.*<br />
																		Editor: Can be viewed only by editors or higher.<br />
																		Administrator: Can only be viewed by administrators.<br />
																		<br />
																		It\'s strongly recommended to flag packets to be open to guests. If you 
																		feel the need to use alternate clearance, do only with a good reason. If you
																		feel a packet has high potential for abuse, set the clearance to patron to minimize
																		risk of abuse.<br /><br />
																		*Patrons are people who have contributed considerably to BNETDocs,
																		but aren\'t editors.'); ?>
				<br />
				<br />
				<table>
					<tbody>
						<tr>
							<td><input type="radio" name="clearance" id="clearance_a" value="6" title="Guest"<? if ($clearance==6) { echo " checked"; } ?>><label for="clearance_a"><img src="images/guesticon.png" alt="Guest" title="Guest" /></label></td>
							<td><input type="radio" name="clearance" id="clearance_b" value="1" title="Member"<? if ($clearance==1) { echo " checked"; } ?>><label for="clearance_b"><img src="images/membericon.png" alt="Member" title="Member" /></label></td>
							<td><input type="radio" name="clearance" id="clearance_c" value="2" title="Patron"<? if ($clearance==2) { echo " checked"; } ?>><label for="clearance_c"><img src="images/patronicon.png" alt="Patron" title="Patron" /></label></td>
							<td><input type="radio" name="clearance" id="clearance_d" value="3" title="Editor"<? if ($clearance==3) { echo " checked"; } ?>><label for="clearance_d"><img src="images/editoricon.png" alt="Editor" title="Editor" /></label></td>
							<td><input type="radio" name="clearance" id="clearance_e" value="4" title="Administrator"<? if ($clearance==4) { echo " checked"; } ?>><label for="clearance_e"><img src="images/adminicon.png" alt="Admin" title="Administrator" /></label></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
    <a href="#top">Back to Top</a>
  </div>
  <div class="tabbox">
    <a name="t2" id="t2"></a>
	<table border=0 cellspacing=2 cellpadding=2 width=100%>
		<tr>
			<td valign=top colspan=2>Format: &nbsp; &nbsp; &nbsp;<font class="required">XML Format Required.</font> &nbsp; &nbsp;
				<? HelpLink('Packet Documentation', 'XML Format<br /><br />XML Format is required. Here\'s an example packet formatted in XML: <br /><br />'.$xmlexample); ?>
				<br />
				<textarea style="width: 95%;" class="commentboxtextarea" name="format" rows="10"><?=$format;?></textarea><br>
			</td>
		</tr>
	</table>
    <a href="#top">Back to Top</a>
  </div>
  <div class="tabbox">
    <a name="t3" id="t3"></a>
	<table border=0 cellspacing=2 cellpadding=2 width=100%>
		<tr>
			<td valign=top colspan=2>Remarks: &nbsp; &nbsp;
				<? HelpLink('Packet Documentation', 'Packet Remarks<br /><br />This part is optional. This is where whatever is needed to be said about the packet would go.'); ?>
				<br />
				<br />
				<textarea style="width: 95%;" class="commentboxtextarea" name="remarks" rows="10"><?=$remarks;?></textarea><br>
			</td>
		</tr>
	</table>
    <a href="#top">Back to Top</a>
  </div>
  <div class="tabbox">
    <a name="t4" id="t4"></a>
	<table border=0 cellspacing=2 cellpadding=2 width=100%>
		<tr>
			<td valign=top colspan=2>Old Document's format: &nbsp; &nbsp; &nbsp;<font class="required">Locked.</font> &nbsp; &nbsp;
				<br />
				<textarea style="width: 95%;" class="commentboxtextarea" name="notused" rows="10" readonly="true"><?=$oldformat;?></textarea><br>
			</td>
		</tr>
		<tr>
			<td valign=top colspan=2>Old Document's remarks: &nbsp; &nbsp; &nbsp;<font class="required">Locked.</font> &nbsp; &nbsp;
				<br />
				<textarea style="width: 95%;" class="commentboxtextarea" name="notused" rows="10" readonly="true"><?=$oldremarks;?></textarea><br>
			</td>
		</tr>
	</table>
    <a href="#top">Back to Top</a>
  </div>
</div>
<br>
<p class="shovethisright"><input type="submit" id="abutton" onclick="this.disabled=false;" value="Submit"></p>
</td>
</tr>
</table>
</form>
</div>
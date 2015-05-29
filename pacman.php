<?
	# Packet Documentation Manager

	# Block Direct Access Attempts
	# -------------------------------
	
		global $auth, $ie, $userid;
		if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		if(rank($userid) < 4) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
		ob_start();
		
		if($_REQUEST['action'] == 'edit') {
			if(!$_POST) {
				# Gather data
				$id = $_REQUEST['id'];
				$msgid = GetInfo('packets', 'id', $_REQUEST['id'], 'messageid');
				$msgname = GetInfo('packets', 'id', $_REQUEST['id'], 'messagename');
				$direction = GetInfo('packets', 'id', $_REQUEST['id'], 'direction');
				$usedbyver1 = GetInfo('packets', 'id', $_REQUEST['id'], 'usedby');
				$usedbyver2 = GetInfo('packets', 'id', $_REQUEST['id'], 'usedby2');
				$oldformat = GetInfo('packets', 'id', $_REQUEST['id'], 'format');
				$oldremarks = GetInfo('packets', 'id', $_REQUEST['id'], 'remarks');
				$remarks = GetInfo('packets', 'id', $_REQUEST['id'], 'remarks');
				$clearance = GetInfo('packets', 'id', $_REQUEST['id'], 'clearance');
				$format = GetInfo('packets', 'id', $_REQUEST['id'], 'xml');
				$status = GetInfo('packets', 'id', $_REQUEST['id'], 'status');
				$action = 'edit';
				
				#used by stuff goes here
				
				$xmldoc = GetInfo('packets', 'id', $_REQUEST['id'], 'xml');
				
				# Display editor 
				include 'if/pakeditorform.dm';
				$out = ob_get_clean();
				DisplayDialogBox('Packet Documentation Manager' , $out);
				return;
			} else {
				function MakeXML($formdata) {

					# Get the data submitted
					
					$packetid = $formdata['packetid'];
					$name = strtoupper($formdata['name']);
					$direction = $formdata['direction'];
					$none = $formdata['none'];
					
					#Get packet type
					
					$pieces = explode("_", $name);
					$packettype = $pieces[0];
					unset($pieces);
					if ($packettype == 'SID') $packettype = 'BNCS';
					
					# Get packet decimal ID
					if (substr($packetid, 0, 2)=="0x") {
						$packetdecid = hexdec(substr($packetid, 2));
					} else if (is_numeric($packetid)) {
						$packetdecid = $packetid;
					} else {
						// Uhh... wtf?
						$packetdecid = 0;
					}
					
					# Make the XML header and footer
					
					$xmlheader = '<?xml version="1.0"?><!DOCTYPE packet SYSTEM "http://labs.bnetdocs.org/dtd/packet.dtd">';
					$xmlfooter = '</packet>';
					
					# Begin assembling the XML
					
					$packetinfo = '<packet type="'.$packettype.'" id="'.$packetid.'" decid="'.$packetdecid.'" name="'.$name.'" direction="'.$direction.'" >';
					$SQL = 'SELECT * FROM `games` ORDER BY `id` ASC';
					$gamearray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
					$i = 0;
					while($row = mysql_fetch_array($gamearray)){
						$gameid = $row['id'];
						${'g' . $gameid} = $_POST['g'.$gameid];
						if(${'g' . $gameid} == 1){ #Used by this game!
							$gameusageinfo .= '<client>'.$row['gamecode'].'</client>';
						}
					}
					
					# Need to implement a relationship system
					
					$format = $formdata['format'];
					
					// BUG FIXED: If remarks was empty, it would present invalid XML formatting.
					
					if (empty($formdata['remarks'])) { $remarks = "<remarks />"; } else {
						$remarks = '<remarks>'.htmlentities($formdata['remarks'], ENT_QUOTES).'</remarks>';
					}
					
					# Need to implement a response identificator system
					
					# Finalize XML assembly
					
					$xml = $xmlheader.$packetinfo.$gameusageinfo.$format.$remarks.$xmlfooter;
					return $xml;
				}
				
				# Get the data submitted
				$id = $_POST['id'];
				$packetid = $_POST['packetid'];
				$name = $_POST['name'];
				$direction = $_POST['direction'];
				$none = $_POST['none'];
				$status = $_POST['status'];
				$clearance = $_POST['clearance'];
				$format = $_POST['format'];
				$remarks = $_POST['remarks'];
				
				
				$SQL = 'SELECT * FROM `games` ORDER BY `id` ASC';
				$gamearray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
				$i = 0;
				while($row = mysql_fetch_array($gamearray)){
					$gameid = $row['id'];
					${'g' . $gameid} = $_POST['g'.$gameid];
				}
				
				# Assemble the XML
				
				if (empty($_POST['format'])) {
					echo "Generating XML formatting...<br />";
					$xml = MakeXML($_POST);
				} else { $xml = $_POST['format']; }
				
				#echo htmlentities($xml, ENT_QUOTES).'<br /><br />';
				
				# Now let's validate the XML
				
				$doc = new DomDocument('1.0'); 
				$doc->preserveWhiteSpace = false;
				$doc->formatOutput = true;
				$doc->loadXML($xml);
				
				if(@$doc->validate()) {
					echo 'The XML has been validated.<br /><br />';
				} else {
					echo 'The XML formatting failed validation.<br /><br />';
				}
				
				echo nl2br(htmlentities($doc->saveXML(), ENT_QUOTES).'<br /><br />');
				
				echo '<br />XML formatting has completed.';
				
				# Let's save all of our work
				
				# Compile the "used by" part of our packet.
				$SQL = 'SELECT * FROM `games` ORDER BY `id` ASC';
				$gamearray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
				$i = 0;
				while($row = mysql_fetch_array($gamearray)){
					$gameid = $row['id'];
					${'g' . $gameid} = $_POST['g'.$gameid];
					if(${'g' . $gameid} == 1){ #Used by this game!
						$gameusageinfo .= ',' . $row['gamecode'];
					}
				} $gameusageinfo = substr($gameusageinfo, 1);
				
				$sql = "UPDATE `packets` SET "
					."`messageid` = '".$_POST['packetid']."',"
					."`messagename` = '".$_POST['name']."',"
					."`direction` = '".$_POST['direction'].","
					."`usedby` = '".$gameusageinfo."',"
					."`status` = '".$status."',"
					."`clearance` = '".$clearance."',"
					."`format` = '".$format."',"
					."`remarks` = '".$remarks."'"
					." WHERE `id` =".$id." LIMIT 1;";
				$save_result = mysql_query($SQL) or die("Update Error: ".mysql_error());
				
				echo "<br />Packet was successfully updated.";
				
			}
		} else if ($_REQUEST['action']=='approve') {
			
			# Set some data for later
			
			$id = $_REQUEST['id'];
			
			# Find this packet first
			
			$sql = 'SELECT * FROM `packets` WHERE `id` ='.$id.' LIMIT 1;';
			$sql = mysql_query($sql) or die('Selection Error: '.mysql_error());
			$row = mysql_fetch_array($sql);
			
			# Check to see if the packet can be approved
			
			$goodxml = 'GOOD'; $badxml = 'BAD'; $noxml = 'NONE';
			
			$xml = $row['xml'];
			if(trim($xml) != '' && $xml){
				# Process it
				$domxml = domxml_open_mem($xml);
				if(is_object($domxml)) {
					$error = array();
					if($domxml->validate($error)) {
						$xmlcheck = $goodxml;
					} else {
						$xmlcheck = $badxml;
					}
				}
			} else { 
				# There's no XML!
				$xmlcheck = $noxml;
			}
			
			# Finalize checking if packet can be approved
			
			if ($xmlcheck!=$goodxml) {
				
				echo '<br />Packet '.$row['messagename'].' ('.$row['messageid'].') cannot be approved because ';
				
				if ($xmlcheck==$badxml) echo 'the XML is bad.';
				else if ($xmlcheck==$noxml) echo 'there is no XML data.';
				else echo 'an XML validation occurred.';
				
			} else {
				
				# Update the packet to be approved
				
				$sql = 'UPDATE `packets` SET '
					.'`approved` ="1"'
					.' WHERE `id` ='.$id.' LIMIT 1;';
				$sql = mysql_query($sql) or die("Update Error: ".mysql_error());
				
				echo "<br />Packet was successfully approved.";
				
			}
		}
		
		# Count how many unapproved packets there are
		
		$sql = "SELECT COUNT(*) as count FROM `packets` WHERE `approved`=0";	# Construct SQL Query
		$count = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
		$count = mysql_fetch_object($count);
		$unapprovedcount = $count->count;
		
		# Display menu (Add, Edit, Delete)
		?>
		<form name="actions" action="/?op=pacman" method="POST">
			<fieldset class="medpanel">
				<legend>INFO Panel</legend>
				Total of unapproved packets: <?=$unapprovedcount;?><br />
				<br />
				<center>
				<input class="optionbutton" type="submit"  name="addpacket" value="Add Packet" /><br /><br />
				</center>
			</fieldset>
		</form>
		<?
		
		# Loop; generate each row with options at end of row
		?>
		<form>
			<fieldset class="normalpanel">
				<legend>Packets Pending Approval</legend>
				<b>Packets cannot be approved without valid existent XML!</b><br /><br />
				Total of unapproved packets: <?=$unapprovedcount;?><br />
				<br />
				<table class="pacman_table">
					<thead><tr><th>XML Status</th><th>ID</th><th>Packet Name</th><th>Direction</th><th>Summary</th><th>Actions</th></tr></thead>
					<tbody>
					<?
						$SQL = 'SELECT * FROM `packets` WHERE `approved`="0" ORDER BY `id` ASC';
						$pakarray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
						while($row = mysql_fetch_array($pakarray)){
							$pakname = $row['messagename'];
							$msgid = $row['messageid'];
							$id = $row['id'];
							$direction = $row["direction"];
							switch ($row["direction"]) {
								case 0: $direction = "Client to Server"; break;
								case 1: $direction = "Server to Client"; break;
								case 2: $direction = "Client to Client"; break;
								default: $direction = "Unknown (".$row["direction"].")";
							}
							$remarks = strip_tags (preg_replace('/[\r\n\t]/e', ' ', ShortenText($row['remarks'], 35)));
							
							$thelink = '<a href="/?op=pacman&action=edit&id='.$id.'">[ '.$msgid.' ] &nbsp; '.$pakname.'</a> &nbsp;  &nbsp;  &nbsp; Summary: '.$remarks.'<br />';
							$optionsbutton = '
								<ul class="cssbutton">
									<li>Options
										<div class="menu">
											<table>
												<tr>
													<td class="option"><a class="CellLink" href="http://www.cnn.com">Edit Packet</a></td>
												</tr>
												<tr>
													<td class="option"><a class="CellLink" href="http://www.cnn.com">Delete Packet</a></td>
												</tr>
											</table>
										</div>
									</li>
								</ul>';
							$badxml = '<span class="infobox">XML<span class="badstatus">&nbsp;&nbsp;BAD&nbsp;</span></span> &nbsp; ';
							$noxml = '<span class="infobox">XML<span class="badstatus">MISSING</span></span> &nbsp; ';
							$goodxml = '<span class="infobox">XML<span class="goodstatus">GOOD</span></span> &nbsp; ';
							
							# Now let's validate the XML
							
							$xml = $row['xml'];
							if(trim($xml) != '' && $xml){
								# Process it
								$domxml = domxml_open_mem($xml);
								if(is_object($domxml)) {
									$error = array();
									if($domxml->validate($error)) {
										$xmlbutton = $goodxml;
									} else {
										$xmlbutton = $badxml;
									}
								}
							} else { 
								# There's no XML!
								$xmlbutton = $noxml;
							}
							//echo $xmlbutton.$thelink;
							echo "<tr><td>".$xmlbutton."</td><td>".$msgid."</td><td>"
								.$pakname."</td><td>".$direction."&nbsp;&nbsp;</td>"
								."<td>".$remarks."</td><td>";
							
							# Display options...
							echo "<a href=\"/?op=pacman&action=edit&id=".$id."\" title=\"Change this packet information.\">Edit</a>";
							if ($xmlbutton==$goodxml) {
								echo " | <a href=\"/?op=pacman&action=approve&id=".$id."\" title=\"Approve this packet into documentation.\">Approve</a>";
							} else {
								echo " | <span title=\"Cannot approve because the XML is missing or bad.\">Approve</span>";
							}
							
							# Continue to end of this packet's entry...
							echo "</td></tr>";
						}
					?>
					</tbody>
				</table>
			</fieldset>
		</form>
		<?

	# End Code
	# -------------		
		$out = ob_get_clean();
		if(!$pagetitle) $pagetitle = 'Packet Documentation Manager';
		DisplayDialogBox($pagetitle, $out);
?>
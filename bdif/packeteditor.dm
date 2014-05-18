			<? 
				global $userid, $rank, $postdate, $title, $content, $edit, $topictype, $action; 
				if(!$topictype) $topictype = 'bnetdocs';
			?>
			<div id="newscontainer">
			<div id="main2">
			<h2>Packet Editor</h2>
			<blockquote><form method="POST" name="packeteditor" action="/?op=packet&action=<?=$action;?><? if($action=='edit') echo '&pid='.$pid; ?>">
			</center>
			<table border=0 cellspacing=2 cellpadding=2 width=100%>
				<tr>
					<td width=0>Message ID:</td><td width=100%><input type="text" name="mid" value="<?=$messageid;?>" size=45 id="inputbox"></td>
				</tr>
				<tr>
					<td>Message Name:</td><td><input type="text" name="mname" value="<?=$messagename;?>" size=45 id="inputbox"></td>
				</tr>
				<tr>
					<td>Direction:</td>
					<td>
						<select name="mdirection" size="1">
						<?
						$sqlquerya = 'SELECT * FROM traffic ORDER BY id';
						$trafficarray = mysql_query($sqlquerya);
						while($rowa = mysql_fetch_array($trafficarray)){
							$tid = $rowa['id'];
							$code = $rowa['code'];
							$descr = $rowa['descr'];
							if($tid == $direction){
								$pdselected = 'selected';
							} else {
								$pdselected = '';
							}
							?><option <?=$pdselected;?> value="<?=$tid;?>"><?=$descr;?></option><?
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td valign=top>Used By:</td><td>
						<!-- Jailout2000: Added "id" attribute and added label tag to the following checkboxes -->
						<input name="scsw" id="scsw" <?=$cbscsw;?> value="1" type="checkbox"><label for="scsw"><img src="/images/games/sware.jpg"></label>&nbsp;&nbsp;&nbsp;&nbsp;<input name="sc" id="sc" <?=$cbsc;?> value="1" type="checkbox"><label for="sc"><img src="/images/games/sc.jpg"></label><br>
						<input name="bw" id="bw" <?=$cbbw;?> value="1" type="checkbox"><label for="bw"><img src="/images/games/starx.jpg"></label>&nbsp;&nbsp;&nbsp;&nbsp;<input name="jsc" id="jsc" <?=$cbjsc;?> value="1" type="checkbox"><label for="jsc"><img src="/images/games/jsc.jpg"></label><br>
						<input name="dsw" id="dsw" <?=$cbdsw;?> value="1" type="checkbox"><label for="dsw"><img src="/images/games/diablosw.jpg"></label>&nbsp;&nbsp;&nbsp;&nbsp;<input name="diablo" id="diablo" <?=$cbdiablo;?> value="1" type="checkbox"><label for="diablo"><img src="/images/games/diablo.jpg"></label><br>
						<input name="d2" id="d2" <?=$cbd2;?> value="1" type="checkbox"><label for="d2"><img src="/images/games/diablo2.jpg"></label>&nbsp;&nbsp;&nbsp;&nbsp;<input name="d2lod" id="d2lod" <?=$cbd2lod;?> value="1" type="checkbox"><label for="d2lod"><img src="/images/games/d2exp.jpg"></label><br>
						<input name="wc2" id="wc2" <?=$cbwc2;?> value="1" type="checkbox"><label for="wc2"><img src="/images/games/war2.jpg"></label>&nbsp;&nbsp;&nbsp;&nbsp;<input name="wc3" id="wc3" <?=$cbwc3;?> value="1" type="checkbox"><label for="wc3"><img src="/images/games/war3.jpg"></label><br>
						<input name="wc3tft" id="wc3tft" <?=$cbwc3tft;?> value="1" type="checkbox"><label for="wc3tft"><img src="/images/games/w3xp.jpg"></label>&nbsp;&nbsp;&nbsp;&nbsp;<input name="wow" id="wow" <?=$cbwow;?> value="1" type="checkbox"><label for="wow"><img src="/images/games/wow.jpg"></label><br>
						<input name="none" id="none" <?=$cnone;?> value="1" type="checkbox" onClick="this.checked=scheck(this.form.list)"><label for="none">None</label><br>
					</td>
				</tr>
				<tr>
					<td valign=top colspan=2>Format:<br>
						<textarea onclick="sz(this);" onkeyup="sz(this)" style="width: 100%;" id="inputbox" name="mformat" rows="10"><?=$format;?></textarea><br>
					</td>
				</tr>
				<tr>
					<td valign=top colspan=2>Remarks:<br>
						<textarea onclick="sz(this);" onkeyup="sz(this)" style="width: 100%;" id="inputbox" rows="10" name="mremarks"><?=$remarks;?></textarea>
					</td>
				</tr>
				<tr>
					<td>Related:</td><td><input type="text" name="mrelated" value="<?=$related;?>" size=45 id="inputbox"> <font size=1>[ <a href="/?op=help&topic=related links">HELP</a> ]</font></td>
				</tr>
				<tr>
					<td nowrap>Packet Status:</td>
					<td>
						<select name="mstatus" size="1">
						<option <?=$psrawselected;?> value="0">Raw</option>
						<option <?=$psnorselected;?> value="1">Normal</option>
						<option <?=$psresselected;?> value="2">Research</option>
						<option <?=$psdefselected;?> value="3">Defunct</option>
						</select>
					</td>
				</tr>
				<tr>
					<td nowrap>Clearance Required:</td>
					<td>
						<select name="mclearance" size="1">
						<option <?=$pcgueselected;?> value="0">Guest</option>
						<option <?=$pcuseselected;?> value="1">User</option>
						<option <?=$pcsupselected;?> value="2">Superuser</option>
						<option <?=$pcediselected;?> value="3">Editor</option>
						<option <?=$pcadmselected;?> value="4">Administrator</option>
						</select>
					</td>
				</tr>
				<tr>
					<td nowrap>Packet Group:</td>
					<td>
						<select name="mgroup" size="1">
						<?
						$sqlquerya = 'SELECT id,groupname FROM groups ORDER BY displayorder';
						$groupsarray = mysql_query($sqlquerya);
						while($rowa = mysql_fetch_array($groupsarray)){
							$gid = $rowa['id'];
							$groupname = $rowa['groupname'];
							if($gid == $pgroupid){
								$pgselected = 'selected';
							} else {
								$pgselected = '';
							}
							?><option <?=$pgselected;?> value="<?=$gid;?>"><?=$groupname;?></option><?
						}
						?>
						</select>
					</td>
				</tr>
			</table>
			<br>
			<input type="submit" id="abutton" onclick="this.disabled=false;" value="Submit"></form>
			</blockquote>
			</div></div>
			<br>
			<? 
				global $userid, $rank, $postdate, $title, $content, $edit, $did, $action; 
			?>
			<div id="newscontainer">
			<div id="main2">
			<h2>Document Editor</h2>
			<blockquote><form method="POST" name="doceditor" action="/?op=doc&action=<?=$action;?><? if($action=='edit') echo '&did='.$did; ?>">
			</center>
			<table border=0 cellspacing=2 cellpadding=2 width=100%>
				<tr>
					<td width=0>Title:</td><td width=100%><input type="text" name="title" value="<?=$title;?>" size=45 id="inputbox"></td>
				</tr>
				<tr>
					<td valign=top>Content:</td>
					<td>
						<textarea onclick="sz(this);" onkeyup="sz(this)" style="width: 100%;" id="inputbox" name="content" rows="10" cols="81"><?=$content;?></textarea>
					</td>
				</tr>
				<tr>
					<td>Related:</td><td><input type="text" name="mrelated" value="<?=$related;?>" size=45 id="inputbox"> <font size=1>[ <a href="/?op=help&topic=related links">HELP</a> ]</font></td>
				</tr>
				<tr>
					<td nowrap>Clearance Required:</td>
					<td>
						<select name="mclearance" size="1">
						<option <?=$dcgueselected;?> value="0">Guest</option>
						<option <?=$dcuseselected;?> value="1">User</option>
						<option <?=$dcsupselected;?> value="2">Superuser</option>
						<option <?=$dcediselected;?> value="3">Editor</option>
						<option <?=$dcadmselected;?> value="4">Administrator</option>
						</select>
					</td>
				</tr>
			</table>
			<br>
			<input type="submit" id="abutton" onclick="this.disabled=false;" value="Submit"></form>
			</blockquote>
			</div></div>
			<br>
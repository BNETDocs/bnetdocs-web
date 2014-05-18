			<? 
				global $userid, $rank, $postdate, $title, $content, $edit, $topictype; 
				if(!$topictype) $topictype = 'bnetdocs';
			?>
			<div id="newscontainer">
			<div id="main2">
			<h2>Post News</h2>
			<div id="author">Posting as <?=whoisid($userid);?> <?=$rank;?> on <?=$postdate;?></div>
			<blockquote><form method="POST" name="thenews" action="/?op=news<?=$edit;?>">
			<center><img src="/images/newsicon/<?=$topictype;?>icon.png" name="thepic"><br>
			<input type="text"  name="subject" value="<?=$title;?>" style="text-align: center; width: 100%;" id="inputbox"></center><br>
			<br>
			<textarea style="width: 100%;" id="inputbox" rows="10" name="text" cols="81"><?=$content;?></textarea><br>
			<br>
			</blockquote>
				<div align=center>
				<table border=0 cellpadding=0 cellspacing=0 width=87%><tr>
				<td>
					Choose News Icon: <select name="pictureselector" size="1" onChange="showimage()">
					<option <?=$bdselected;?> value="bnetdocs">BNETDocs</option>
					<option <?=$bnselected;?> value="bnet">Battle.net</option>
					<option <?=$bnlsselected;?> value="bnls">BNLS</option>
					<option <?=$scselected;?> value="starcraft">Starcraft</option>
					<option <?=$scselected;?> value="starcraft2">Starcraft II</option>
					<option <?=$wcselected;?> value="warcraft">World of Warcraft</option>
					<option <?=$wcselected;?> value="war3">Warcraft III</option>
					<option <?=$dselected;?> value="diablo">Diablo</option>
					<option <?=$scselected;?> value="blizz">Blizzard</option>
					</select>
				</td>
				<td align=right>
					<input type="submit" id="abutton" onclick="this.disabled=false;" value="Post"></form>
				</td>
				</tr></table><br></div>
			</div></div>
			<br>
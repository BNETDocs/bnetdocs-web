			<? global $catid, $subid; ?>
			<center id="comments">
				Feel free to engage in detailed questions and discussion!<br>
				<br>
				<?
					if($userid){
						echo '<a href="#postcomment">Got Something To Say?</a>';
					} else {
						echo 'Got something to say? Log in to comment!';
					}
				?>
			</center>
		</div>
		<h4 class="nobr">USER COMMENTS</h4>
		<div class="nTBar"></div>
		<?
			if($misc){
				echo '<table class="commenttable" border=0 cellpadding=0 cellspacing=0 width="100%">';
				echo $misc;
				echo '</table>';
			} else {
				echo '<br /><br /><center>No comment posted, be the first to comment!</center><br /><br />';
			}
			if($userid) {
		?>
			<div class="nBBar"></div>
			<div class="thebody">
				<form class="commentbox" method="post" name="newcomment" action="/?op=postcomment">
					<div class="buttons">
						<span id="postcomment">Post A Comment: </span><br><br>
						<input name="catid" value="<?=$catid;?>" type="hidden">
						<input name="subid" value="<?=$subid;?>" type="hidden">
						<center>
							<textarea name="mycomment" class="commentboxtextarea" rows="10" cols="81"></textarea><br>
						</center>
						<br>
						<input id="abutton" style="" value="Submit" type="submit"><br>
					</div>
					<br>
				</form>
			</div>
		<?
		}
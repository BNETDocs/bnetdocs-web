<? if($misc == 'fade'){ ?><div class="fadethis"><? } ?>
<div class="dialogboxbg" style="width: <? if($mini){ echo '200px';  } else { echo '100%'; } ?>">
	<div class="TLCorner"></div>
	<div class="TBar"></div>
	<div class="TRCorner"></div>
	<div <? if(!$mini) { ?> class="dialogboxbg2" <? } ?>>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="LSide"><div class="clear"></div></td>
				<td class="content">
					<?
						if(!$mini){
							if($title){
								?><h2><?=$title;?></h2><?
							}
							if($iconpath){
								?><div class="punch"><img class="punch" src="<?=$iconpath;?>" alt="" /></div><?
							}
						} else {
							if($title){
								?><h5 class="subheader"><?=$title;?></h5><?
							}
						}
					?>
						<div class="thebody">
							<?
								if(!$file){
									echo $text;
								} else {
									include $text;
								}
							?>
						</div>
					<?
						if($footer){
							?><div class="bottombar"><?=$footer;?></div><?
						}
					?>
				</td>
				<td class="RSide"><div class="clear"></div></td>
			</tr>
		</table>
	</div>
	<div class="BLCorner"></div>
	<div class="BBar"></div>
	<div class="BRCorner"></div>
</div>
<? if($misc == 'fade'){ ?></div><? } ?>
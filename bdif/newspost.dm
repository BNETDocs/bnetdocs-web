<? global $author, $rank, $postdate, $icon, $title, $content, $nid, $userid; ?>
<div id="newscontainer">
<div id="main2">
<h2><?=$title;?></h2>
<div id="author">Posted by <?=whoisid($author);?> <?=$rank;?> on <?=$postdate;?></div>
<img id="newsicon" src="/images/newsicon/<?=$icon;?>icon.png" alt="icon" align="left">
<p><?=$content;?></p>
<?
	if($userid){
		if(rank($userid) > 2){
			?>
			<div id="author" align=right >
			<div class="menu">
			<? 
			if($userid == $author || $userid == 1){ 
				?>
				<a href="/?op=news&mode=edit&nid=<?=$nid;?>"><span>Edit</span></a>
				<? 
			} 
			if((rank($userid) > 4) || ($userid == $author)){
				?>
				<a onClick="return confimdelete();" href="/?op=news&mode=delete&nid=<?=$nid;?>"><span>Delete</span></a>
				<?
			}
		}
	}
?>
<!--<a href="#"><span>View Comments</span></a>-->
</div></div></div></div>
<br>
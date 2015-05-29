<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');

	# Begin Code
	# -------------

	$topic = $_GET['topic'];
	
	#Render Dialog box header
		?>
		<div id="newscontainer">
		<div id="main2">
		<h2>Help Topic: <?=$topic;?></h2>
		<blockquote>
		<?
		
	#No Clearance section
	
	#Clearance section
	if($userid){
		if($topic == 'related links'){
			if(rank($userid) > 2){
				echo '
					<i>When you add a packet or edit a packet, you will see a section
					that says "Related:".<br></i>
					<br>
					<b><font size=3 color=red>What the hell is this?</b><br></font>
					<br>
					<i>It\'s short for	related links. <br></i>
					<br>
					<b><font size=3 color=red>Okay, thanks for stating the obvious, now how the hell do I 
					put a related link in there? The stupid thing keeps	screwing up my links!</b><br></font>
					<br>
					<i>Okay, seems like some educational spewing is in order. I\'ll keep
					this as simple as possible. Separate links with a comma. There also are
					short cuts you can make use of.<br></i>
					<br>
					<b><font size=3 color=red>@#$%@#$ Well dammnit, I\'ve died and gone to hell. 
					HOW THE HELL DO I DO SHORTCUTS?!? HOW MANY TIMES DO I HAVE TO REPEAT MYSELF?!?</b><br></font>
					<br>
					<i>P stands for packet. D stands for documents. You put down the letter
					pertaining to the type of info you\'re linking to, then you put down the
					id of the document/packet you\'re linking to. To get the id of a document
					or packet, use the following instructions:<br>
					<br>
					1. Look at the navigation menu.<br>
					2. Find the link that goes to the packet/document you want to link to.<br>
					3. Hover the mouse over the link. In the status bar of your browser, 
					you will see the address the link will send you to. See the PID or DID part of
					the address? That\'s the id of the packet/document. <br>
					<br>
					Here\'s an example:<br>
					<br>
					Let\'s say we want to link to this packet:<br>
					<br>
					<a href="http://bnetdocs.dementedminds.net/?op=packet&pid=370">http://bnetdocs.dementedminds.net/?op=packet&pid=370</a><br></i>
					<br>
					<b><font size=3 color=red>Well, shit son...</b><br></font>
					<br>
					<i>Calm down. Almost done. See the pid=370 part of the link? The packet id is 370.
					Now, for related, type down P370, meaning packet number 370.<br>
					<br>
					Don\'t forget: if you\'re linking to multiple packets/documents, make sure you
					separate each packet/document link with a comma (,). <br></i>
					<br>
					<b><font size=3 color=red>Shit, son...</b><br></font>
					<br>
					<i>Complicated, I know. But it works. And it means if packets get moved around, or the address gets changed, at least the links won\'t get broken.<br></i>
					<br>
					<b><font size=3 color=red>I hate you.</b><br></font>
					<br>
					<i>Sorry. At least it works? :-)<br></i>
					';
			} else {
				blockhack();
			}
		} elseif($topic == 'quick jump'){
			?>
			Quick Jump...<br>
			<br>
			It's actually not that hard to use. Type in the exact packet name and click go, and it'll take you
			right to the packet. If you mistype the packet name, it'll just display an error message.
			It's case insensitive. Enjoy.
			<?
		} else {
			echo 'Unknown topic.';
		}
	} else {
		blockhack();
	}
	
	# Render dialog box footer
		?></blockquote>
		</div></div>
		<br><?
		
	# All done!
?>
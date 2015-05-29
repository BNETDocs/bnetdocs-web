<?
	# This will be freaking complicated, due to tons and tons of detection mechanisms involved. Fun, fun.
	# But in the end, the packet menu will be entirely automated, so there will be no need for an admin to maintain the packet menu.
	# The whole point of my coding a CMS for bnetdocs was to ***minimize*** any need for administration, thus my heavy focus on
	# automation...
	
		# Grab list of packet groups
		$pktmenu_SQL = 'SELECT * FROM `packetgroups` ORDER BY `id` ';
		$pktmenu_menuarray = mysql_query($pktmenu_SQL)  or die("Selection Error: ".mysql_error());
		
		# Begin generating menu 
		
		while($pktmenu_packetgroup = mysql_fetch_array($pktmenu_menuarray)){
			
			$pktmenu_caption = $pktmenu_packetgroup['categoryname'];	# Grab packet group name
			$pktmenu_tag = $pktmenu_packetgroup['tag'];	# Grab packet tag
			$tagstrlen = strlen($pktmenu_tag);	# Get length of tag for use in SQL
			
			# Grab list of packets in particular group
			$pktmenu_SQLPackets = "SELECT * FROM `packets` WHERE LEFT(messagename, $tagstrlen) ='$pktmenu_tag'";
			$pktmenu_packets = mysql_query($pktmenu_SQLPackets)  or die("First Selection Error: ".mysql_error());
			
			$numofpackets = mysql_num_rows($pktmenu_packets);	# Get total of packets in group 

			if($numofpackets > 0){
				echo '<li class="submenu"><a href="#nogo">'.$pktmenu_caption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
				echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
			}  else {
				#echo '<li class="standardmenu"><a href="'.$rowFirstLevel['link'].'">'.$pktmenu_caption.'</a>'."\n";
			}
			
			if($numofpackets > 0) {
				# do loop to generate further levels for the menu
				
				$direction = 0; # Start with the first direction
				
				while($direction < 3){
				
					# There are three directions, need to identify them
					if($direction == 0) $subcategorycaption = 'Client -> Server';
					if($direction == 1) $subcategorycaption = 'Server -> Client';
					if($direction == 2) $subcategorycaption = 'Client -> Client';
					
					# Grab packets in packet group and particular direction
					$pktmenu_SQLDIRPackets = "SELECT * FROM `packets` WHERE LEFT(messagename, $tagstrlen) ='$pktmenu_tag' AND `direction`=$direction AND `approved`=1";
					$pktmenu_DIRPackets = mysql_query($pktmenu_SQLDIRPackets)  or die("Second Selection Error: ".mysql_error());

					$numofdirpackets = mysql_num_rows($pktmenu_DIRPackets);	# Get total of packets in direction 

					if($numofdirpackets > 0){
						echo '<li class="submenu"><a href="#nogo">'.$subcategorycaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
						echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
					}
					
					# Now do final loop to display packet list
					
					if($numofdirpackets > 0){ # To reduce unnecessary processing
						echo '<div class="packetlist">';
						while($packet = mysql_fetch_array($pktmenu_DIRPackets)){
							$packetcaption = $packet['messagename'];
							$packetid = $packet['messageid'];
							$packetdatabaseid = $packet['id'];
							echo '<li class="standardmenu"><a href="/?op=packet&id='.$packetdatabaseid.'">'.$packetcaption.' ('.$packetid.')</a></li>'."\n";
						}
						echo '</div>';
					}
					
					if($numofdirpackets > 0){
						echo '</ul><!--[if lte IE 6]></td></tr></table><![endif]--></li>'."\n";
					}
					
					# Go to the next direction
					$direction++;
				}
				
			}
			
			if($numofpackets > 0){
				echo '</ul><!--[if lte IE 6]></td></tr></table><![endif]--></li>'."\n";
			}
		}
?>
<?
	# This will be freaking complicated, due to tons and tons of detection mechanisms involved. Fun, fun.
	# But in the end, the packet menu will be entirely automated, so there will be no need for an admin to maintain the packet menu.
	# The whole point of my coding a CMS for bnetdocs was to ***minimize*** any need for administration, thus my heavy focus on
	# automation...
	

				while($rowFirstLevel = mysql_fetch_array($menuarrayHorizional)){
					$FirstLevelitemcaption = $rowFirstLevel['itemcaption'];

					if(!$userid && $Horizionalitemcaption == 'Console') break;
						
					# Generate first level menu item

					$SQLFirstLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowFirstLevel['id'].'" ORDER BY `position` ';
					$menuarrayFirstLevel = mysql_query($SQLFirstLevel)  or die("Selection 1 Error: ".mysql_error());
					$subcategories3 = mysql_num_rows($menuarrayFirstLevel);
					$L3=$subcategories3;
				
					if($subcategories3 > 0){
						echo '<li class="submenu"><a href="'.$rowFirstLevel['link'].'">'.$FirstLevelitemcaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
						echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
					}  else {
						echo '<li class="standardmenu"><a href="'.$rowFirstLevel['link'].'">'.$FirstLevelitemcaption.'</a>'."\n";
					}
					
					while($rowSecondLevel = mysql_fetch_array($menuarrayFirstLevel)){
						$SecondLevelitemcaption = $rowSecondLevel['itemcaption'];

						# Generate second level menu item
						
						$SQLSecondLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowSecondLevel['id'].'" ORDER BY `position` ';
						$menuarraySecondLevel = mysql_query($SQLSecondLevel)  or die("Selection 2 Error: ".mysql_error());
						$subcategories4 = mysql_num_rows($menuarraySecondLevel);
						$L4=$subcategories4;

						if($subcategories4 > 0){
							echo '<li class="submenu"><a href="'.$rowSecondLevel['link'].'">'.$SecondLevelitemcaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
							echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
						}  else {
							echo '<li class="standardmenu"><a href="'.$rowSecondLevel['link'].'">'.$SecondLevelitemcaption.'</a>'."\n";
						}
						
						while($rowThirdLevel = mysql_fetch_array($menuarraySecondLevel)){
							$ThirdLevelitemcaption = $rowThirdLevel['itemcaption'];

							# Generate third level menu item			
							
							$SQLThirdLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowThirdLevel['id'].'" ORDER BY `position` ';
							$menuarrayThirdLevel = mysql_query($SQLThirdLevel)  or die("Selection 3 Error: ".mysql_error());
							$subcategories5 = mysql_num_rows($menuarrayThirdLevel);
							$L5=$subcategories5;
							
							if($subcategories5 > 0){
								echo '<li class="submenu"><a href="'.$rowThirdLevel['link'].'">'.$ThirdLevelitemcaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
								echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
								
							}  else {
								echo '<li class="standardmenu"><a href="'.$rowThirdLevel['link'].'">'.$ThirdLevelitemcaption.'</a>'."\n";
							}

							while($rowFourthLevel = mysql_fetch_array($menuarrayThirdLevel)){
								$FourthLevelitemcaption = $rowFourthLevel['itemcaption'];

								# Generate fourth level menu item			

								$SQLFourthLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowFourthLevel['id'].'" ORDER BY `position` ';
								$menuarrayFourthLevel = mysql_query($SQLFourthLevel)  or die("Selection 3 Error: ".mysql_error());
								$subcategories6 = mysql_num_rows($menuarrayFourthLevel);
								$L6=$subcategories6;
								
								if($subcategories6 > 0){
									echo '<li class="submenu"><a href="'.$rowFourthLevel['link'].'">'.$FourthLevelitemcaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
									echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
									
								}  else {
									echo '<li class="submenu"><a href="'.$rowFourthLevel['link'].'">'.$FourthLevelitemcaption.'</a>'."\n";
								}
								
								while($rowFifthLevel = mysql_fetch_array($menuarrayFourthLevel)){
									# Generate fifth level menu item			
									$FifthLevelitemcaption = $rowFifthLevel['itemcaption'];
									
									# No more depth
									
									echo '<li class="standardmenu"><a href="'.$rowFifthLevel['link'].'">'.$FifthLevelitemcaption.'</a>'."\n";
									
									#---- End of loop
									echo '</li>'."\n";
								}
								
								#---- End of loop
								if($subcategories6 > 0){
									echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]--></li>'."\n";
								}  else {
									echo '</li>'."\n";
								}
							}

							#---- End of loop
							if($subcategories5 > 0){
								echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]--></li>'."\n";
							}  else {
								echo '</li>'."\n";
							}
							$L4--;
						}
						
						#---- End of loop
						if($subcategories4 > 0){
							echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]--></li>'."\n";
						}  else {
							echo '</li>'."\n";
						}
						$L3--;
					}
					
					#---- End of loop
					if($subcategories3 > 0){
						echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]--></li>'."\n";
					}  else {
						echo '</li>'."\n";
					}
					$L2--;
				}
?>
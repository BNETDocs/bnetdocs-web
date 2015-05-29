<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/xhtml; charset=UTF-8" />
<title>BNETDocs</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<?
	$HUA = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : ''."\n";
	if (eregi("msie",$HUA) ||	eregi("internet explorer",$HUA)) {
		$ie = true;
	} else { 
		$ie = false;
	} 
	if (strpos($HUA, 'opera') !== false) {
		$opera = true;
	} else {
		$opera = false;
	}
	if (strpos($HUA, 'chrome') !== false) {
		$chrome = true;
	} else {
		$chrome = false;
	}		
	if($ie){
		?>
		<!--[if IE]>
		<style type="text/css">
			/* IE */
			.buttons input{
			    width: auto;
			    overflow: visible;
				padding: 2px 2px 2px 2px;
				font-size: 10px;
			}
		</style>
		<![endif]--><?
	}
	if($opera){
		# No need for this part for now, but left in for in case of future need.
	}
	if($chrome){
		?><!-- Chrome specific table render fix -->
		<style>
			body:nth-of-type(1) .codetable {
				margin-top: 30px;
			}
		</style>
		<!-- End of Chrome-specific fix --><?
	}
?>
<script type="text/javascript" src="js/domtab.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js" type="text/javascript"></script>

	<style type="text/css">
		@import "css/domtab.css";
	</style>
<!--[if gt IE 6]>
	<style type="text/css">
		html>body ul.domtabs a:link,
		html>body ul.domtabs a:visited,
		html>body ul.domtabs a:active,
		html>body ul.domtabs a:hover{
			height:3em;
		}
	</style>
<![endif]-->
	
	<?
	if(javascriptok($userid)){ 
		#Javascript header code goes in javascript.dm. We're using JQuery for BNETDocs, no other libraries will be used, this is to ensure no conflict occurs in the JS code.
		include 'javascript.dm';
   } 
   ?>
</head>
<body background="images/stripe.png">
<div id="headcontainer">
	<table cellpadding= "0" border= "0" cellspacing= "0" width="100%">
		<tr>
			<td class="logo">
				<img src="images/bnetdocslogo.png" alt="" />
			</td>
			<td class="rightheader">
				<form name="quickjump" class="quickjump" action="http://www.bnetdocs.org/?op=packet" method="post">
					<div class="buttons">
						Quick Jump 
						<? HelpLink('Quick Jump', 'Quick Jump<br /><br />This feature is on hold.'); ?>
						: <input tabindex="1" name="quickjump" value="" type="text" class="qkjump" />
						<input tabindex="2" type="submit" value="Jump" />
					</div>
				</form>
			</td>
		</tr>
	</table>
</div>
<table cellspacing= "0" cellpadding= "0" border= "0" width="100%"><tr><td id="bgmenu">
	<!-- Start of menu section -->
	<div id="menu_container">
		<ul id="nav">
		<?
		
		# Generate Menu Layout here
		$SQL = 'SELECT * FROM `menusystem` WHERE `parent`="0" ORDER BY `position` ';
		$menuarray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
		$L1 = 0;
		$subcategories = mysql_num_rows($menuarray);
		$L1=$subcategories;
		while($rowHorizional = mysql_fetch_array($menuarray)){
			$Horizionalitemcaption = $rowHorizional['itemcaption'];
			# Generate horizional level menu item
			$SQLHorizional = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowHorizional['id'].'" ORDER BY `position` ';
			$menuarrayHorizional = mysql_query($SQLHorizional)  or die("Selection Error: ".mysql_error());
			
			$subcategories2 = mysql_num_rows($menuarrayHorizional);
			$L2=$subcategories2;
			$HLclass = ''."\n";
			$LIclass = ''."\n";
			if($Horizionalitemcaption == 'Console'){
				if($userid){ 
					if($subcategories2 > 0){
						echo '<li class="rootmenu"><a href="'.$rowHorizional['link'].'">'.$Horizionalitemcaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
						echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
					}  else {
						echo '<li class="rootmenu"><a href="'.$rowHorizional['link'].'">'.$Horizionalitemcaption.'</a>'."\n";
					}
				}  else {
					?>
					<li class="rootmenu"><a href="#nogo">Login<!--[if gte IE 7]><!--></a><!--<![endif]-->
						<!--[if lte IE 6]><table><tr><td><![endif]-->
						<ul>
							<li>
								<div class="expand">
									<div class="buttons">
										<form name="quickjump" id="loginform" action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
											Username:<br />
											<input tabindex="1" name="username" value="" type="text" class="qkjump" /><br />
											<div class="linebr"></div>
											Password:<br />
											<input tabindex="2" name="password" value="" type="password" class="qkjump" /><br />
											<div class="linebr"></div>
											<input tabindex="3" type="submit" value="Login" /> &nbsp;<input tabindex="4" id="inputbox" name="rememberme" value="yes" type="checkbox" />Remember Me
											<input type="hidden" name="operation" value="login" />
											<input type="hidden" name="lastloc" value="<?=$lastloc;?>" />
										</form>
									</div>
								</div>
							</li>
						</ul>
						<!--[if lte IE 6]></td></tr></table></a><![endif]-->
					</li>
					<?
				}
			} else {
				if($subcategories2 > 0){
					echo '<li class="rootmenu"><a href="'.$rowHorizional['link'].'">'.$Horizionalitemcaption.'<!--[if gte IE 7]><!--></a><!--<![endif]-->'."\n";
					echo '<!--[if lte IE 6]><table><tr><td><![endif]--><ul>'."\n";
				}  else {
					echo '<li class="rootmenu"><a href="'.$rowHorizional['link'].'">'.$Horizionalitemcaption.'</a>'."\n";
				}
			}
			if($Horizionalitemcaption == 'Packets'){
				# Generate packets menu. Do not remove # from both, only choose one. 
				
				#include 'packetmenu.dm'; 		#temp holder to bury errors
				include 'packetmenuexp.dm'; 	# The one I'm working on, incompleted
				
			} else {
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
			}
			
			#---- End of loop
			if($Horizionalitemcaption != 'Console'){
				if($subcategories2 > 0){
					echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]--></li>'."\n";
				}  else {
					echo '</li>'."\n";
				}
			} else {
				if($userid){
					if($subcategories2 > 0){
						echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]--></li>'."\n";
					}  else {
						echo '</li>'."\n";
					}
				}
			}
			$L1--;
		}
		
		?>
			</ul>
		</div>
		<!-- End of menu section -->
</td></tr></table>
<br /><br />
<table border= "0" cellpadding= "0" cellspacing= "0" width="100%">
<tr>
<td id="maincontent" valign="top">
<div class="Container2">
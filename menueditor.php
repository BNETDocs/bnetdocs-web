<?
	# Menu Editor

	# Block Direct Access Attempts
	# -------------------------------
	
		global $auth, $ie, $userid;
		if($auth != 'true') die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
		if(rank($userid) < 4) die('<html><head><title>BNETDocs: Redux</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
		ob_start();
		
		# Process menu editing if any
		if($_POST){
			$itemcaption = $_POST['itemcaption'];	# Mandatory
			$link = $_POST['link'];	# Not mandatory
			$parent = $_POST['parent'];	# Mandatory, parent must exist.
			$position = $_POST['position'];	# Mandatory
			$clearance = $_POST['clearance'];	# Not Mandatory
			if($clearance == 0) $clearance = false;
			$id = $_POST['id'];
			if(!$link || trim($link) == '') $link = '#nogo';
			if($_POST['operation'] == 'addeditmenuitem'){
				if($id && trim($id) != ''){
					# Edit menu item
					$SQLString = 'UPDATE menusystem SET ';
					if($itemcaption) $SQLString .= 'itemcaption = "'.$itemcaption.'", ';
					if($link) $SQLString .= 'link = "'.htmlspecialchars($link).'", ';
					if($parent) $SQLString .= 'parent = "'.$parent.'", ';
					if($position) $SQLString .= 'position = "'.$position.'", ';
					if($clearance) $SQLString .= 'clearance = "'.$clearance.'", ';
					$SQLString = substr($SQLString, 0, strlen($SQLString) - 2);
					$SQLString .= ' WHERE `id`='.$id;
					#echo $SQLString.'<br /><br />';
					mysql_query($SQLString) or die("Menu Edit MySQL Error: ".mysql_error());
					echo 'Menu item edited.<br /><br />';
				} else {
					if(!$itemcaption || !$parent || !$position){
						echo 'Unable to process add request, missing fields.<br />';
					} else {
						$SQL = "INSERT INTO menusystem (itemcaption, link, parent, position, clearance) VALUES ('$itemcaption','$link','$parent','$position','$clearance')";
						mysql_query($SQL) or die("Menu Insert MySQL Error: ".mysql_error());
						echo 'Menu item added.<br /><br />';
					}
				}
			} else {
				$sql = "SELECT COUNT(*) AS count FROM `menusystem` WHERE `id`=$id";
				$count = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
				$count = mysql_fetch_object($count);
				$count = $count->count;
				if($count > 0){
					$itemcaption = GetInfo('menusystem', 'id', $id, 'itemcaption');
					$SQL = "DELETE FROM menusystem WHERE `parent`='$id' OR `id`='$id'";
					mysql_query($SQL) or die ('Menu Delete MySQL Error: '.mysql_error());
					echo 'Menu item removed.<br /><br />';
				} else {
					echo 'Unable to comply with delete request. No menu item matching description was found. Your parameters were: ItemCaption: '.$itemcaption.', Parent: '.$parent.'<br /><br />';
				}
			}
		}
		
		# Determine how many menu items there are
		
		$sql = "SELECT COUNT(*) AS count FROM `menusystem`";
		$count = mysql_query($sql) or die("mysql error in $sql: ".mysql_error());
		$count = mysql_fetch_object($count);
		$count = $count->count;
		echo 'Generating menu map...    <br />';
		echo "There is a total of $count menu items. <br /><br />The numbers to the left of each menu item are their ID numbers. <br  />The numbers to the right of each menu item are their position number within the menu.<br /><br />";
		
		$SQL = 'SELECT * FROM `menusystem` WHERE `parent`="0" ORDER BY `position` ASC';
		$menuarray = mysql_query($SQL)  or die("Selection Error: ".mysql_error());
		while($rowHorizional = mysql_fetch_array($menuarray)){
			$Horizionalitemcaption = $rowHorizional['itemcaption'];
			# Generate horizional level menu item
			echo $rowHorizional['id'].' '.'<a href="'.$rowHorizional['link'].'">'.$Horizionalitemcaption.'</a>  --  '.$rowHorizional['position'].' &nbsp;  -- Clearance: <img src="'.getrankimage($rowHorizional['clearance']).'" /> <br />';
			$SQLHorizional = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowHorizional['id'].'" ORDER BY `position` ASC';
			$menuarrayHorizional = mysql_query($SQLHorizional)  or die("Selection Error: ".mysql_error());
			while($rowFirstLevel = mysql_fetch_array($menuarrayHorizional)){
				$FirstLevelitemcaption = $rowFirstLevel['itemcaption'];
				# Generate first level menu item
				echo '&nbsp;&nbsp;&nbsp;'.$rowFirstLevel['id'].' '.'<a href="'.$rowFirstLevel['link'].'">'.$FirstLevelitemcaption.'</a>  --  '.$rowFirstLevel['position'].' &nbsp;  -- Clearance: <img src="'.getrankimage($rowFirstLevel['clearance']).'" /> <br />';
				$SQLFirstLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowFirstLevel['id'].'" ORDER BY `position` ASC';
				$menuarrayFirstLevel = mysql_query($SQLFirstLevel)  or die("Selection 1 Error: ".mysql_error());
				while($rowSecondLevel = mysql_fetch_array($menuarrayFirstLevel)){
					$SecondLevelitemcaption = $rowSecondLevel['itemcaption'];
					# Generate second level menu item
					echo '&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.$rowSecondLevel['id'].' '.'<a href="'.$rowSecondLevel['link'].'">'.$SecondLevelitemcaption.'</a>  --  '.$rowSecondLevel['position'].' &nbsp;  -- Clearance: <img src="'.getrankimage($rowSecondLevel['clearance']).'" /> <br />';
					$SQLSecondLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowSecondLevel['id'].'" ORDER BY `position` ASC';
					$menuarraySecondLevel = mysql_query($SQLSecondLevel)  or die("Selection 2 Error: ".mysql_error());
					while($rowThirdLevel = mysql_fetch_array($menuarraySecondLevel)){
						$ThirdLevelitemcaption = $rowThirdLevel['itemcaption'];
						# Generate third level menu item
						echo '&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.$rowThirdLevel['id'].' '.'<a href="'.$rowThirdLevel['link'].'">'.$ThirdLevelitemcaption.'</a>  --  '.$rowThirdLevel['position'].' &nbsp;  -- Clearance: <img src="'.getrankimage($rowThirdLevel['clearance']).'" /> <br />';
						$SQLThirdLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowThirdLevel['id'].'" ORDER BY `position` ASC';
						$menuarrayThirdLevel = mysql_query($SQLThirdLevel)  or die("Selection 3 Error: ".mysql_error());
						while($rowFourthLevel = mysql_fetch_array($menuarrayThirdLevel)){
							$FourthLevelitemcaption = $rowFourthLevel['itemcaption'];
							# Generate fourth level menu item
							echo '&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.$rowFourthLevel['id'].' '.'<a href="'.$rowFourthLevel['link'].'">'.$FourthLevelitemcaption.'</a>  --  '.$rowFourthLevel['position'].' &nbsp;  -- Clearance: <img src="'.getrankimage($rowFourthLevel['clearance']).'" /> <br />';
							$SQLFourthLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowFourthLevel['id'].'" ORDER BY `position` ASC';
							$menuarrayFourthLevel = mysql_query($SQLFourthLevel)  or die("Selection 4 Error: ".mysql_error());
							while($rowFifthLevel = mysql_fetch_array($menuarrayFourthLevel)){
								$FifthLevelitemcaption = $rowFifthLevel['itemcaption'];
								# Generate fifth level menu item
								echo '&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.'&nbsp;&nbsp;&nbsp;'.$rowFifthLevel['id'].' '.'<a href="'.$rowFifthLevel['link'].'">'.$FifthLevelitemcaption.'</a>  --  '.$rowFifthLevel['position'].' &nbsp;  -- Clearance: <img src="'.getrankimage($rowFifthLevel['clearance']).'" /> <br />';
								#$SQLFifthLevel = 'SELECT * FROM `menusystem` WHERE `parent`="'.$rowFifthLevel['id'].'" ORDER BY `position` ASC';
								#$menuarrayFifthLevel = mysql_query($SQLFifthLevel)  or die("Selection 5 Error: ".mysql_error());
							}
						}
					}
				}
			}
		}
		echo '<br />Done! <br /><br />';
		
		# Menu Editor here
		
		?>
		<table cellpadding="10px" width="100%">
			<tr>
				<td>
					<form name="menueditor" action="?op=menueditor" method="post">
						<b><u>Add Menu Item</u></b><br />
						<br />
						<i>If you need a menu item to be edited, specify the item ID along with whatever changes you want to make. If you're adding a new item, leave item ID blank.</i><br />
						<br />
						Menu Item Caption:<br />
						<input tabindex="1" name="itemcaption" value="" type="text" class="qkjump" /><br />
						<div class="linebr"></div>
						Link:<br />
						<input tabindex="2" name="link" value="" type="text" class="qkjump" /><br />
						<div class="linebr"></div>
						Parent ID #:<br />
						<input tabindex="3" name="parent" value="" type="text" class="qkjump" /><br />
						<div class="linebr"></div>
						Position:<br />
						<input tabindex="4" name="position" value="" type="text" class="qkjump" /><br />
						<div class="linebr"></div>
						Item ID #:<br />
						<input tabindex="4" name="id" value="" type="text" class="qkjump" /><br />
						<div class="linebr"></div>
						Clearance:<br />
						<select name="clearance" size="1">
							<option selected value="0">Default</option>
							<option value="6">Guest</option>
							<option  value="1">User</option>
							<option  value="2">Superuser</option>
							<option  value="3">Editor</option>
							<option  value="4">Administrator</option>
						</select><br />
						<div class="linebr"></div>
						<input tabindex="5" type="submit" value="Add / Edit" />
						<input type="hidden" name="operation" value="addeditmenuitem">
					</form>
				</td>
				<td width="50%" valign="top">
					<form name="menueditor" action="?op=menueditor" method="post">
						<b><u>Remove Menu Item</u></b><br />
						<br>
						<i>Be warned: if you remove a menu item that has been set as a parent (has subcategories to it), those subcategories attached to it will
						be removed as well!</i><br />
						<br />
						Item ID:<br />
						<input tabindex="4" name="id" value="" type="text" class="qkjump" /><br />
						<div class="linebr"></div>
						<input tabindex="5" type="submit" value="Remove" />
						<input type="hidden" name="operation" value="removemenuitem">
					</form>
				</td>
			</tr>
		</table>
		<?
		$out = ob_get_clean();
		DisplayDialogBox('BNETDocs Menu Editor' , $out);
?>
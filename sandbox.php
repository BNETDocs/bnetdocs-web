<?
	# Block Direct Access Attempts
	# -------------------------------
	global $auth, $ie;
	if($auth != 'true') die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	if(!$userid) die('<html><head><title>BNETDocs</title></head><body bgcolor=black><table border=0 valign=center align=center width=100% height=100%><tr><td valign=center align=center><font color=red><b>Direct Access Denied. Nice try buddy!</b></font></td></tr></table></body></html>');
	
	# Begin Code
	# -------------
	
	# Administrators only, block out the rest.
	
	if(rank($userid) < 4){
		error("There's nothing in the sandbox.");
		return;
	}
	
	# Experimenting with string comparsion
	
	$firststring = "This is the first string.";
	$secondstring = "This is the second string.";
	$i = GetStrDiff($firststring, $secondstring);
	if($i){
		$a = split($i, '|');
		$final = $a[0].' was changed to '.$a[1];
	} else {
		$final = "There was no change between the two strings.";
	}
	
	$finalstring =  "First string: $firststring<br>Second string: $secondstring<br>Differences: $final<br><br>Function result: ";
	
	if(!$i){
		if($firststring != $secondstring){
			$finalstring .= 'Failure. Function claims no change, but there was a change.';
		} else {
			$finalstring .= 'Success!';
		}
	} else {
		if($firststring == $secondstring){
			$finalstring .= 'Failure. Function claims there was changes, but there was none.';
		} else {
			$finalstring .= 'Success!';
		}
	}
	
	displaydialogbox($finalstring, "Sandbox: String Comparsion Experiment");
	
?>
<?
	$str = 'This is a string.';
	echo 'Before encoding: '.$str.'<br>';
	$str = base64_encode($str);
	echo 'After encoding: '.$str.'<br>';
	$str = base64_decode($str);
	echo 'After decoding using base64_decode: '.$str.'<br>';
	echo 'Done.';
?>
<?php

//Say the image file name is in the 
//$origpng variable. Get the dimensions.

$origpng = 'bnlsicon.png';

$imgsize = getimagesize($origpng);

$newheight = round($imgsize[1]);

$newwidth = round($imgsize[0]);

//Initialize the image.

$image = imagecreatefrompng($origpng);

//Initialize the new image.

#echo $newheight.' '.$newwidth;
#die();
$newimage = imagecreate($newwidth, $newheight);

//Allocate some nice black space.

$black = imagecolorallocate($image, 0,0,0);

//Resize the old image into the new image, 
//specifying starting points and dimensions.

imagecopyresized($newimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $imgsize[0], $imgsize[1]);

//Get rid of the old image.

imagedestroy($image);

//Now, the message -- the second argument, 
//a number from 1-5, specifies a built-in font. 
//Or you can load your own with ImageLoadFont().
//We'll put it in the upper right with coordinates of 10,10.

imagestring($newimage, 2, 10, 10, "Copyright 2001", $black);

//All righty, let's wrap things up here.

header("Content-type: image/png");

imagepng($newimage);

imagedestroy($newimage);

//Voila!

?>
<?php

function clean($var){
	$var = mysql_real_escape_string($var);
	$var = stripslashes($var);
	$var = htmlentities($var);
	return $var;
}

function clean_filename($var) {
	return str_replace (' ', '-', clean($var));
}

function output_image($question, $answer, $dest_img, $quality = 5) {
	//Set the Content Type
	header('Content-type: image/jpeg');
		
	if (file_exists($dest_img)) {
	  echo 'file should be displayed directly if the image exists in the folder';
	
	} else {
		$dest_img = clean_filename($question) . '++' . clean_filename($answer) . 'jpg';
		
		// Create Image From Existing File
		$jpg_image = imagecreatefromjpeg('images/leklek_empty.jpg');
		
		// Allocate A Color For The Text
		$white = imagecolorallocate($jpg_image, 255, 255, 255);
		
		// Set Path to Font File
		//$font_path = 'font.TTF';
		
		// Print Question On Image
		imagettftext($jpg_image, 0, 129, 44, 100, $white, $font_path, $question);
		
		// Print Answer On Image
		imagettftext($jpg_image, 8, 300, 67, 100, $white, $font_path, $answer);
		
		// Send Image to Browser
		imagejpeg($jpg_image, $dest_img, $quality);
		
		// Clear Memory
		imagedestroy($jpg_image);
	}
}
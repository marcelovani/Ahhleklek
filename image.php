<?php

function output_image($question, $answer, $dest_img, $type, $quality = 50) {
	
    if (file_exists($type . $dest_img)) {
    	// Load image
        $jpg_image = imagecreatefromjpeg($dest_img);
    } else {
        //$dest_img = clean_filename($question) . '_' . clean_filename($answer) . 'jpg';
        
        // Create Image From Existing File
        $jpg_image = imagecreatefromjpeg('images/leklek_empty.jpg');
        
        // Allocate A Color For The Text
        $color = imagecolorallocate($jpg_image, 0, 0, 0);
        
        // Set Path to Font File
        $font_file = 'fonts/basicsanssfbold/basicsanssfbold.ttf';
        
        // Print Question On Image
        $font_size = 12;
        $max_width = 85;
        $x = 140;
        $y = 30;
     	$line_space = 17;
		multiline_print($jpg_image, sentence($question), $font_file, $font_size, $color, $x, $y, $max_width, $line_space);

        // Print Answer On Image
        $font_size = 15;
        $max_width = 135;
        $x = 300;
        $y = 55;
     	$line_space = 20;
     	multiline_print($jpg_image, sentence($answer), $font_file, $font_size, $color, $x, $y, $max_width, $line_space);

		if ($type == 'thumb') {
			$thumb_image = imagecreatetruecolor(200, 200);
			imagecopyresampled($thumb_image, $jpg_image, 0, 0, 120, 0, 200, 200, 300, 300);
			$jpg_image = $thumb_image;
	    } else {
	        // Save Image on folder
    	    imagejpeg($jpg_image, $dest_img, $quality);
    	}
    }
    
    //Set the Content Type
    header('Content-type: image/jpeg');
	// Send Image to Browser
    imagejpeg($jpg_image, NULL, $quality);        
    
    // Clear Memory
    imagedestroy($jpg_image);    
	
	exit;
}

function multiline_print($jpg_image, $text, $font_file, $font_size, $color, $x, $y, $max_width, $line_space) {
	$words = explode(" ",$text);
	$wnum = count($words);
	$line = '';
	$text='';
	for($i=0; $i<$wnum; $i++){	
	  $line .= $words[$i];
	  $dimensions = imagettfbbox($font_size, 0, $font_file, $line);
	  $lineWidth = $dimensions[2] - $dimensions[0];
	  $lineHeight = $dimensions[6] - $dimensions[1];
	  if ($lineWidth > $max_width) {
	    $text.=($text != '' ? '|'.$words[$i].' ' : $words[$i].' ');
	    $line = $words[$i].' ';
	  }
	  else {
	    $text.=$words[$i].' ';
	    $line.=' ';
	  }
	}
	$lines = explode('|', $text);
	foreach ($lines as $key => $line) {
		imagettftext($jpg_image, $font_size, 0, $x, $y, $color, $font_file, $line);
		$y = $y + $lineHeight + $line_space;
	}	
}
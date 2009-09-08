<?php

require_once ('../../config.php');
require_once ('lib.php');

$vizalgoid = required_param('vizalgo', PARAM_INT); // algorithm visualization ID
$vizalgo = get_record('vizcosh_vizalgos', 'id', $vizalgoid);

// =========================================================================
// Open image file according to file extension
// =========================================================================
if (isset ($vizalgo->thumbnail) && (strcmp($vizalgo->thumbnail,"default") != 0)) {
	if(isset($vizalgo->fnthumbnail)){
		$extension = substr(strrchr($vizalgo->fnthumbnail, "."), 1);
		echo $vizalgo->thumbnail;
		switch($extension){
			case "gif":
				Header("Content-Type: image/gif");
				$thumb_dec = $vizalgo->thumbnail;
				$img = imagecreatefromstring($thumb_dec);
				ImageGIF($img);
				break;
			case "jpeg":
				Header("Content-Type: image/jpeg");
				$thumb_dec = $vizalgo->thumbnail;
				$img = imagecreatefromstring($thumb_dec);
				ImageJPEG($img);
				break;
			case "png":
				Header("Content-Type: image/png");
				$thumb_dec = $vizalgo->thumbnail;
				$img = imagecreatefromstring($thumb_dec);
				ImagePNG($img);
				break;
		}
	}
}
// =========================================================================
// Open image file according to file extension END
// =========================================================================
?>

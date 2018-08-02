<?php 
// ob_start('ob_gzhandler');   
// ob_start(); 
// die;
require('../index-template.php');
$config_main = require(ROOT_PATH . '/frontend/modules/'.temp.'/config/main.php');
$size_backend = require('size.php');

$size = array_merge($size_backend, $config_main['size-image']);
	

if(isset($_GET['i']) && isset($_GET['h']) && isset($_GET['w'])){
	$i = $_GET['i'];
	$w = $_GET['w'];
	$h = $_GET['h'];
	$ok = '';

	// http://localhost/20170715/imgthumbnail/20/30/test-blog-130.jpg

	foreach ($size as $key => $value) {
		if($value[0] == $w && $value[1] == $h) $ok = 'ok';
	}

	if($i != '' && $ok == 'ok'){
		$fileOut = '../uploads/'.$i;

		$fileOutThumbLink = '../uploads/'.$w.'/'.$h;
		$fileOutThumbImg = '../uploads/'.$w.'/'.$h.'/'.$i;

		if (!file_exists($fileOutThumbLink)) {
		    mkdir($fileOutThumbLink, 0777, true);
		}

		if (file_exists($fileOutThumbImg)) {	
		    $imageInfo = getimagesize($fileOutThumbImg);
		    switch ($imageInfo[2]) {
		        case IMAGETYPE_JPEG:
		            header("Content-Type: image/jpeg");
		            break;
		        case IMAGETYPE_GIF:
		            header("Content-Type: image/gif");
		            break;
		        case IMAGETYPE_PNG:
		            header("Content-Type: image/png");
		            break;
		       default:
		            break;
		    }
		    header('Content-Length: ' . filesize($fileOutThumbImg));
		    readfile($fileOutThumbImg);
		    die;			
		}else{
			if (file_exists($fileOut)) {			
				header('Content-Type: image/png');
				create_thumbnail($fileOut,$fileOutThumbImg,$w,$h);	
				create_thumbnail($fileOut,false,$w,$h);	
			}
		}		
	}
	die;
}else{
	die;
}

function create_thumbnail($path, $savelink, $width, $height) {
	$info = getimagesize($path);
	$size = array($info[0], $info[1]);
	

	if ($info['mime'] == 'image/png') {
		$src = ImageCreateFromPNG($path);		
	} 
	if ($info['mime'] == 'image/jpeg') {
		$src = ImageCreateFromJPEG($path);		
	}
	if ($info['mime'] == 'image/gif') {
		$src = ImageCreateFromGIF($path);		
	} 	

	// echo $info['mime'];die;

	if($height == 0){
		$height = (int)($width * $info[1] / $info[0]);
	}

	$thumb = imagecreatetruecolor($width, $height);

	$src_aspect = $size[0] / $size[1];
	$thumb_aspect = $width / $height;

	if ($src_aspect < $thumb_aspect) {

		$scale = $width / $size[0];
		$new_size = array($width, $width / $src_aspect);
		$src_post = array(0, ($size[1] * $scale - $height) / $scale / 2);

	} else if ($src_aspect > $thumb_aspect) {

		$scale = $width / $size[1];
		$new_size = array($height * $src_aspect, $height);
		$src_post = array(($size[0] * $scale - $width) / $scale / 2, 0);

	} else {
		$new_size = array($width, $height);
		$src_post = array(0, 0);
	}

	$new_size[0] = max($new_size[0], 1);
	$new_size[1] = max($new_size[1], 1);

	$transparent = imagecolorallocate($thumb,255,255,255);
		imagecolortransparent($thumb,$transparent);
		imagefilledrectangle($thumb,0,0,4000,4000,$transparent);

	imagecopyresampled($thumb, $src, 0, 0, $src_post[0], $src_post[1], $new_size[0], $new_size[1], $size[0], $size[1]);

	if($savelink === false){
		if ($info['mime'] == 'image/png') {
			return imagepng($thumb);	
		} 
		return imagejpeg($thumb);
		
	} else {
		if ($info['mime'] == 'image/png') {
			return imagepng($thumb, $savelink);	
		} 		
		return imagejpeg($thumb, $savelink);
		
	}




}

 ?>
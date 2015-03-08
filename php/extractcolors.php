<?php
/* extracted from pixelpost addon */
/* WARNING: There is no input validation of $_GET */


$c = Extract_Colors($_GET['img']);
?>
<html>
    <head>
	<title>Color Extractor</title>
	<style type="text/css">
	    body { 
		font-family: sans-serif;
		background-color: <?php echo $c["dark"] ?>; 
		color: <?php echo $c["light"] ?>;
	    }

	    .dark {
		background-color: <?php echo $c["dark"] ?>; 
		color: <?php echo $c["light"] ?>; 
		border-top: 1px solid <?php echo $c["normal"] ?>;	
		border-left: 1px solid <?php echo $c["normal"] ?>;	
		border-bottom: 1px solid <?php echo $c["normal"] ?>;	
	    }

	    .normal {
		background-color: <?php echo $c["normal"] ?>; 
		color: <?php echo $c["light"] ?>; 
		border-top: 1px solid <?php echo $c["normal"] ?>;	
		border-bottom: 1px solid <?php echo $c["normal"] ?>;	
	    }

	    .light {
		background-color: <?php echo $c["light"] ?>; 
		color: <?php echo $c["dark"] ?>; 
		border-top: 1px solid <?php echo $c["light"] ?>;	
		border-bottom: 1px solid <?php echo $c["light"] ?>;	
	    }

	    .box {
		width:200px;
		height:100px;
		line-height:100px;
		float:left;
		text-align: center;
		vertical-align: middle;
	    } 

	    .colorBox {
		margin-top:20px;
		clear:both;
		margin-bottom:30px;
	    }
	</style>
    </head>
    
    <body>
	<h1>Color Extractor</h1>
	<img src="<?php echo $_GET['img'] ?>" />
	<div class="colorBox">
	    <div class="box dark">Dark: <?php echo $c["dark"] ?></div>
	    <div class="box normal">Normal <?php echo $c["normal"] ?></div>
	    <div class="box light">Light <?php echo $c["light"] ?></div>
	</div>
    </body>
</html>


<?php
exit(0);

function Extract_Colors($image_path) {
         $colors=Get_Color($image_path);
         $colors_key=array_keys($colors);
         $mykey = $colors_key[0];
	 
	 if($mykey == 0) {
                 $mykey = $colors_key[1];
         }

         if($mykey != 0) {                                               /*      for color images                */
                 $primary_color_dark[0] = $mykey;                        /*      [0] is Hue ( 0-360 )            */
                 $primary_color_dark[1] = .15;                           /*      [1] Brightness ( 0.0-1.0 )      */
                 $primary_color_dark[2] = .2;                            /*      [2] is Saturation ( 0.0-1.0 )   */
                 $primary_color_normal[0] = $mykey;
                 $primary_color_normal[1] = .25;
                 $primary_color_normal[2] = .15;
                 $primary_color_bright[0] = $mykey;
                 $primary_color_bright[1] = .95;
                 $primary_color_bright[2] = 1.0; 
         } else {                                                        /*      for black and white images      */
                 $primary_color_dark[0] = $mykey;
                 $primary_color_dark[1] = .15;
                 $primary_color_dark[2] = .0001;                         /* <--  do not edit this value          */
                 $primary_color_normal[0] = $mykey;
                 $primary_color_normal[1] = .25;
                 $primary_color_normal[2] = .0001;                       /* <--  do not edit this value          */
                 $primary_color_bright[0] = $mykey;
                 $primary_color_bright[1] = .95;
                 $primary_color_bright[2] = .0001;                       /* <--  do not edit this value          */
         }

         $primary_color_dark_hex = rgb2hex(hls2rgb($primary_color_dark));
         $primary_color_normal_hex = rgb2hex(hls2rgb($primary_color_normal));
         $primary_color_bright_hex = rgb2hex(hls2rgb($primary_color_bright));
	 
	 return array("dark" => $primary_color_dark_hex, "normal" => $primary_color_normal_hex, "light" => $primary_color_bright_hex);
}

/**
 * Color analyzer for input image, Returns the colors of the image in an array, ordered in descending order, where the keys are the colors, and the values are the count of the color.
 *
 * @return array
 * @param  string $myimage path to image
 * @access public
 */
function Get_Color($myimage) {
        $PREVIEW_WIDTH    = 150;  //WE HAVE TO RESIZE THE IMAGE, BECAUSE WE ONLY NEED THE MOST SIGNIFICANT COLORS.
        $PREVIEW_HEIGHT   = 150;
        $size = getimagesize($myimage);
        $scale=1;
        
	if ($size[0] > 0) {
                $scale = min($PREVIEW_WIDTH/$size[0], $PREVIEW_HEIGHT/$size[1]);
	}

        if ($scale < 1) {
                $width = floor($scale*$size[0]);
                $height = floor($scale*$size[1]);
        } else {
                $width = $size[0];
                $height = $size[1];
        }

        $image_resized = imagecreatetruecolor($width, $height);
        if ($size[2]==1) {
                $image_orig=imagecreatefromgif($myimage);
	}
        
	if ($size[2]==2) {
                $image_orig=imagecreatefromjpeg($myimage);
	}
        
	if ($size[2]==3) {
                $image_orig=imagecreatefrompng($myimage);
	}

        imagecopyresampled($image_resized, $image_orig, 0, 0, 0, 0, $width, $height, $size[0], $size[1]); //WE NEED NEAREST NEIGHBOR RESIZING, BECAUSE IT DOESN'T ALTER THE COLORS
        $im = $image_resized;
        $imgWidth = imagesx($im);
        $imgHeight = imagesy($im);

        for ($y=0; $y < $imgHeight; $y++){
                for ($x=0; $x < $imgWidth; $x++){
                        $index = imagecolorat($im,$x,$y);
                        $Colors = imagecolorsforindex($im,$index);
                        $myRGB[0] = $Colors['red'];
                        $myRGB[1] = $Colors['green'];
                        $myRGB[2] = $Colors['blue'];
                        $myhls = rgb2hls($myRGB);
                        $huearray[] = "" . (intval($myhls[0]/16))*16;
                }
        }

        $huearray=array_count_values($huearray);
        natsort($huearray);
        $huearray=array_reverse($huearray,true);

        return $huearray;
}

/**
 * Convert an RGB array into HLS colour space.
 *
 * Expects array(r, g, b) where r, g, b in [0,255].  The HLS array is
 * returned as array(h, l, s) where h is in [0,360], l and s in [0,1].
 *
 * Function adapted from 'Computer Graphics: Principles and Practice',
 * by Foley, van Dam, Feiner and Hughes.  Chapter 13; Achromatic and
 * Colored Light.
 *
 * @return array
 * @param  array $rgb
 * @access public
 */
function rgb2hls($rgb){
        for ($c=0; $c<3; $c++) {
                $rgb[$c] = $rgb[$c] / 255;
        }
        $hls = array(0, 0, 0);
        $max = max($rgb);
        $min = min($rgb);
        $hls[1] = ($max + $min) / 2;
        if ($max == $min) {
                $hls[0] = null;
                $hls[2] = 0;
        } else {
                $delta = $max - $min;
                $hls[2] = ($hls[1] <= 0.5) ? ($delta / ($max + $min)) : ($delta / (2 - ($max + $min)));
                if ($rgb[0] == $max) {
                        $hls[0] = ($rgb[1] - $rgb[2]) / $delta;
                } else if ($rgb[1] == $max) {
                        $hls[0] = 2 + ($rgb[2] - $rgb[0]) / $delta;
                } else {
                        $hls[0] = 4 + ($rgb[0] - $rgb[1]) / $delta;
                }
                $hls[0] *= 60;
                if ($hls[0] < 0) {
                        $hls[0] += 360;
                }
                if ($hls[0] > 360) {
                        $hls[0] -= 360;
                }
        }
        ksort($hls);
        return $hls;
}

/**
 * Convert HLS colour space array to RGB colour space.
 *
 * Expects HLS array  as array(h, l, s) where h in [0,360], l and s each
 * in [0,1].  Returns array(r, g, b) where r, g, and b each in [0, 255]
 *
 * Function adapted from 'Computer Graphics: Principles and Practice',
 * by Foley, van Dam, Feiner and Hughes.  Chapter 13; Achromatic and
 * Colored Light.
 *
 * @return array
 * @param  array $hls
 * @access public
 */
function hls2rgb($hls){
        $rgb = array(0, 0, 0);
        $m2 = ($hls[1] <= 0.5) ? ($hls[1] * (1 + $hls[2])) : ($hls[1] + $hls[2] * (1 - $hls[1]));
        $m1 = 2 * $hls[1] - $m2;
        if (!$hls[2]) {
                if ($hls[0] === null) {
                        $rgb[0] = $rgb[1] = $rgb[2] = $hls[1];
                } else {
                        return false;
                }
        } else {
                $rgb[0] = _hVal($m1, $m2, $hls[0] + 120);
                $rgb[1] = _hVal($m1, $m2, $hls[0]);
                $rgb[2] = _hVal($m1, $m2, $hls[0] - 120);
        }
        for ($c=0; $c<3; $c++) {
                $rgb[$c] = round($rgb[$c] * 255);
        }
        return $rgb;
}

/**
 * Hue value checker for HSL colour space routine.
 *
 * @return float
 * @param  float $n1
 * @param  float $n2
 * @param  float $h
 * @access private
 * @see    Image::hls2rgb()
 */
function _hVal($n1, $n2, $h){
        if ($h > 360) {
                $h -= 360;
        } else if ($h < 0) {
                $h += 360;
        }
        if ($h < 60) {
                return $n1 + ($n2 - $n1) * $h / 60;
        } else if ($h < 180) {
                return $n2;
        } else if ($h < 240) {
                return $n1 + ($n2 - $n1) * (240 - $h) / 60;
        } else {
                return $n1;
        }
}

/**
 * Convert a hex colour string into an rgb array.
 *
 * Handles colour string in the following formats:
 *
 *     o #44FF55
 *     o 4FF55
 *     o #4F5
 *     o 4F5
 *
 * @return array
 * @param  string $hex
 * @access public
 */
function hex2rgb($hex) {
        $hex = @preg_replace('/^#/', '', $hex);
        if (strlen($hex) == 3) {
                $v = explode(':', chunk_split($hex, 1, ':'));
                return array(16 * hexdec($v[0]) + hexdec($v[0]), 16 * hexdec($v[1]) + hexdec($v[1]), 16 * hexdec($v[2]) + hexdec($v[2]));
        } else {
                $v = explode(':', chunk_split($hex, 2, ':'));
                return array(hexdec($v[0]), hexdec($v[1]), hexdec($v[2]));
        }
}

/**
 * Convert an rgb array into a hex colour string.
 *
 * Handles colour string in the following formats:
 *
 *     o #44FF55
 *     o 4FF55
 *     o #4F5
 *     o 4F5
 *
 * @return array
 * @param  string $hex
 * @access public
 */
function rgb2hex($rgb, $adHash = true){
        return sprintf("%s%02X%02X%02X", ($adHash ? '#' : ''), $rgb[0], $rgb[1], $rgb[2]);
}
?>

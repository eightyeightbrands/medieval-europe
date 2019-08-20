<?php
if ( $img )
{
	header("Content-type:image/jpeg");
	imagejpeg($img, null, 100);
	imagedestroy( $img );
}
?>
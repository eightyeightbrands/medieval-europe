<?php
foreach ($sheets as $key => $val)
	{ echo html::stylesheet('media/css/'.$key, $val, FALSE); }	
echo $content;
?>
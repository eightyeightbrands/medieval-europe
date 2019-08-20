<div class="pagetitle"><?php echo kohana::lang('structures.region_lawlist') . " - " . kohana::lang( $structure->region->kingdom -> get_name() ) ?></div>

<?php echo $submenu ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('/castle/laws/'.$structure->id, kohana::lang('structures_castle.viewlaws'))?>
&nbsp;&nbsp;
<?php echo html::anchor('/castle/addlaw/'.$structure->id, kohana::lang('structures.region_addlaw'))?>
</div>

<br/>

<?php
if ( $structure->region->laws->count() == 0 )
	echo '<p><i>' . kohana::lang('structures.region_nolawsfound') . '</i></p>' ;
else
{
?>

<?php

$i=0;
foreach ($structure->region->laws as $law )
{
	echo "<h4>".$law->name."</h4>";
	echo "<p>".nl2br($law->description)."</p>";
	echo 
	"<p>"
		.html::anchor("/castle/editlaw/" . $structure->id. "/" .  $law->id, kohana::lang('global.edit')) . "&nbsp;&nbsp;"
		.html::anchor("/castle/deletelaw/". $structure->id . "/" . $law->id, kohana::lang('global.delete'), array('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')')).		
	"</p>";
	
	echo html::image(array('src' => 'media/images/template/hruler.png'));

}
?>
<br/>
<?php
}
?>

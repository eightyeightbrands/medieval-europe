<div class="pagetitle"><?php echo kohana::lang('character.mypropertiespagetitle') ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='helper'><?php echo kohana::lang('character.myproperties_helper');?></div>

<br/>

<div style='margin:0 auto;width:90%' class='center'>

<?php 
$k = 0;
foreach ( $properties as $property )
{	 
?>
<div style='float:left;width:33%;text-align:center;height:120px;margin-bottom:10px;border:0px solid #fff'>
<?
	if ( $property -> supertype  == 'terrain' )
		$imagepath = 'media/images/structures/' . $property -> image . '_' . $property -> attribute1 . '.jpg' ;		
	else
		$imagepath = 'media/images/structures/' . $property -> image ;
	
	echo html::image( 
		$imagepath, 
		array('class' => 'size75 border', 
		'style' => 'vertical-align:middle' ) );
	echo '<br/>';
	echo kohana::lang( $property -> name ) . ' - ' . kohana::lang( $property -> kingdom_name ) . ', ' . kohana::lang( $property -> region_name )
?>
</div>
<?
}
?>
<br style="clear:both;" />
</div>
<br style="clear:both;" />

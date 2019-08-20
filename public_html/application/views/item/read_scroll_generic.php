<div class= 'pagetitle'><?php echo kohana::lang('global.read'); ?></div>

<? 
	echo html::anchor( 
		'character/inventory/',
		'Back',
		array('class' => 'button button-small')
	); 
?>

<br/><br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<div id='messageboardcontainertop_normal'>
</div>

<div id= 'messageboardcontainer_normal'>

<div style='padding:10px'>
	
<center><h3><?php echo $bodycontent['scroll_title']; ?></h3></center>
<br/>

<?php echo  kohana::lang( $item -> cfgitem -> name ) .' N. '. $bodycontent['scroll_id']?>
	
	
	<p style="margin-top: 15px"><?php echo $bodycontent['scroll_body']; ?></p>	
  
	<?php echo $bodycontent['scroll_date']; ?>
	<br style="margin-bottom:5px; margin-top:15px;">
	<?php echo $bodycontent['scroll_signature']; ?>
</div>
</div>

<div id='messageboardcontainerbottom_normal'>
</div>	

<br style="clear:both;" />

	

	

<div class="pagetitle"><?php echo kohana::lang('structures.region_lawlist') . " - " . kohana::lang( $structure->region->kingdom -> get_name() ) ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php echo html::anchor('royalpalace/viewlaws/'.$structure->id, kohana::lang('structures_royalpalace.viewlaws'),
array('class' => 'selected')); ?>
<?php echo html::anchor('royalpalace/addlaw/'.$structure->id, kohana::lang('structures.region_addlaw')); ?>
<?php echo html::anchor('royalpalace/taxes/'.$structure->id, kohana::lang('structures_royalpalace.submenu_taxes'))?>
</div>
<?php
if ( $laws -> count() == 0 )
	echo '<p><i>' . kohana::lang('structures.region_nolawsfound') . '</i></p>' ;
else
{
?>

<div class='pagination'><?php echo $pagination->render('extended') ?> </div>

<br/>

<?php
$i=0;
foreach ( $laws as $law )
{
?>	

	<div id='messageboardcontainertop_normal'></div>	
		<div id='messageboardcontainer_normal'>	
		<h5 class='center'><?php echo $law->name ?></h5>
		<div style='margin-top:5px;padding:0px 10px'>		
		<?php echo html::image(array('src' => 'media/images/template/hruler.png'));?>
		<br/><br/>
		<p>
		<?php 
			echo 
				html::anchor("royalpalace/editlaw/" . $structure->id. "/" .  $law->id, '[' . kohana::lang('global.edit') . ']') . "&nbsp;&nbsp;" .
				"&nbsp;" . 
				html::anchor("royalpalace/deletelaw/". $structure->id . "/" . $law->id, '[' . kohana::lang('global.delete' ) . ']', array('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'));		
			echo '<br/><br/>';
			echo Utility_Model::bbcode( $law -> description ); 
		?>	
			
		</p>
		<br/>
		<?php 
			if ( $law -> signature != '' )
			{
				echo kohana::lang('global.lawcreatedon', 
					Utility_Model::format_datetime( $law -> timestamp ));
			}
			
			if ( !empty( $law -> signature ) )
			{
				echo "<hr style='margin-bottom:5px'/>";
				echo Utility_Model::bbcode( $law -> signature );
			}		
		?>
		</div>
	</div>
	<div id='messageboardcontainerbottom_normal'></div>
	<br style='clear:both'/>
	
<?php 
} 
?>
<?php
}
?>
<br style='clear:both'/>

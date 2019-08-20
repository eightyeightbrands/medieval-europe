<script type="text/javascript">
$(document).ready(function()
{	
	$("#tabs").tabs({active: 0});		
});
</script>

<div class="pagetitle"><?= kohana::lang( $structure -> structure_type -> name) ?> - <?= kohana::lang($structure -> region -> name); ?></div>

<div id="helper">
<?php echo kohana::lang('structures_academy.study_helper'); ?>&nbsp; 
	<?php echo kohana::lang('taxes.appliablevalueaddedtax', $appliabletax ) ?>
</div>


<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>

<div id='tabs'>
	
	
	<ul>
	<? foreach ($availablecourses as $installedcourse) { ?>
		<li><?php echo html::anchor("#tab-{$installedcourse}", kohana::lang('structures.course_' . $installedcourse. '_name'));?></li>
	<? } ?>
	</ul>
	
	<? foreach ($availablecourses as $installedcourse) { ?>
	<div id='tab-<?=$installedcourse;?>'>	
		<? 			
			$obj  = CourseFactory_Model::create($installedcourse); 		
			echo $obj -> helper_show( $char, $structure ); 
			// Processa evento study
			if ( $obj -> getLevel( $char ) == 21 )
			{
				$_par[0] = $obj -> getTag();
				GameEvent_Model::process_event( $char, 'study', $_par );	
			}
			
		?>	
	</div>
	<? } ?>

</div>
	
<br style="clear:both;" />

<h2 class="center">
<?php 
echo kohana::lang('structures.' . $structure -> structure_type -> type . '_' . 
		$structure -> structure_type -> church -> name );
?>
</h2>
<br/>
<div id='helperwithpic'>
	
	<div id='locationpic'>
	<?php echo html::image('media/images/template/locations/' . $structure -> structure_type -> type . 
	'_' . $structure -> structure_type -> church -> name . '.jpg' ); ?>
	</div>
	
	<fieldset>
	<legend><?php echo kohana::lang('religion.religionstats')?> </legend>
	<?php 
		echo kohana::lang('religion.followers') . ': <b>' .  
		html::anchor(
			'/religion_1/viewinfo/' . $structure -> structure_type -> church_id,
			$info['followers'],
			array('target' => 'new')
			) . '</b>' ; 
	?>
	<br/>
	<?php echo kohana::lang('religion.percentage') . ': <b>' .  $info['percentage'] . '%</b>' ; ?>
	</fieldset>

	<fieldset>
	<legend><?php echo kohana::lang('religion.structurestats')?></legend>
	<?php echo kohana::lang('religion.faithpoints') . ': <b>' ;
	if ( ! Structure_Model::get_stat_d( $structure -> id, 'faithpoints' ) -> loaded )
		echo 0 ;
	else
		echo Structure_Model::get_stat_d( $structure -> id, 'faithpoints' ) -> value ; 
	echo  '</b>';?>
	<br/>
	<?php 
	echo kohana::lang('religion.accumulatedfaithpoints') . ': <b>' ; 
	if ( ! Structure_Model::get_stat_d( $structure -> id, 'fpcontribution' ) -> loaded )
		echo 0 ;
	else
		echo Structure_Model::get_stat_d( $structure -> id, 'fpcontribution' ) -> value ; 
	echo  '</b>'; ?>
	</fieldset>
</div>

<br style='clear:both'/>

<br/>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>




<script type="text/javascript">
$( function() 
{
	
	if ( $('#defendmode').is(":checked") )
	{
		$(".fightmodehelper").hide();
		$("#defendhelper").show();		
	}
	
	if ( $('#attackmode').is(":checked") )
	{
		$(".fightmodehelper").hide();
		$("#attackhelper").show();	
	}
	
	if ( $('#normalmode').is(":checked") )
	{
		$(".fightmodehelper").hide();		
	}
	
	$("#defendmode").click(function()
	{
		$(".fightmodehelper").hide();
		$("#defendhelper").show();
	}),
	$("#attackmode").click(function()
	{		
		$(".fightmodehelper").hide();
		$("#attackhelper").show();	
	}),
	$("#normalmode").click(function()
	{
		$(".fightmodehelper").hide();		
	});
});
</script>

<div class="pagetitle"><?php 
	echo kohana::lang('structures_battlefield.battlefield_pagetitle')?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<div class="submenu center">
<?php 
if ( $battle -> status == 'completed' and $battle -> type == 'raid'  )
	echo html::anchor( "/battlefield/raidloot/" . $structure -> id , Kohana::lang('structures_battlefield.raidloot'))  
?>

<?php echo html::anchor( "/battlefield/joinfaction/attack/" . $structure -> id, Kohana::lang('structures_battlefield.joinattackers') )?>

<?php echo html::anchor( "/battlefield/joinfaction/defend/" . $structure -> id, Kohana::lang('structures_battlefield.joindefenders') )?>

<?php echo html::anchor( "/battlefield/entercity/" . $structure -> id , Kohana::lang('structures_battlefield.entercity'))  ?>
</div>

<br/>

<div id='helperwithpic'>
	
	<div id='locationpic'>
		<?php echo html::image('media/images/template/locations/battlefield.jpg') ?>
	</div>

	<div id='helper'>

		<?php 

			if ( $battle -> status == 'completed' ) 
				echo kohana::lang( 'structures_battlefield.battlefield_battlecompleted_helper',
					kohana::lang($targetregion -> name ));
			else
				echo 
				kohana::lang( 'structures_battlefield.battlefield_helper' , 
					kohana::lang($targetregion -> name ), 
					$attackers_count,
					$joinedcharacters['attack']['attackerorally'], 
					$joinedcharacters['attack']['mercenary'], 
					$joinedcharacters['attack']['native'], 
					$defenders_count, 
					Utility_Model::countdown( $timetostart )); ?>

	</div>

</div>

<br style='clear:both'/>

<fieldset>
<legend><?php echo Kohana::lang('structures_battlefield.attackers_list')?></legend>
	<?php
	if ( $attackers == '' )
		echo kohana::lang('structures_battlefield.noattackers'); 
	else
		echo $attackers ;
	?>
</fieldset>

<br/>

<fieldset>
<legend><?php echo Kohana::lang('structures_battlefield.defenders_list')?></legend>
	<?php
	if ( $defenders == '' )
		echo kohana::lang('structures_battlefield.nodefenders'); 
	else
		echo $defenders ;
	?>
</fieldset>

<br/>

<fieldset>

<legend>
	<?= kohana::lang('structures_battlefield.fightmode'); ?>
</legend>

<?= kohana::lang('structures_battlefield.currentfightmode');?> <span class='value'><?= kohana::lang('structures_battlefield.fightmode_' . $fightmode); ?></span>
<br/><br/>
<span>
<?= kohana::lang('structures_battlefield.configurefightmode');?>
</span>
<br/>
<?= form::open(); ?>
<?= kohana::lang('structures_battlefield.fightmode_normal');?> 
<?= form::radio(
	array(
		'name' => 'fightmode',
		'value' => 'normal',
		'id' => 'normalmode',
		'checked' => ($fightmode=='normal'? true: false)
		)
	); ?>
<? if ($hasdogma_meditateanddefend) { ?>
<?= kohana::lang('structures_battlefield.fightmode_defend');?> 
<?= form::radio(
	array(
		'name' => 'fightmode',
		'value' => 'defend',
		'id' => 'defendmode',
		'checked' => ($fightmode=='defend'? true: false)
		)
	); ?>
<? } ?>
<? if ($hasdogma_killtheinfidels) { ?>
<?= kohana::lang('structures_battlefield.fightmode_attack');?> 
<?= form::radio(
	array(
		'name' => 'fightmode',
		'value' => 'attack',
		'id' => 'attackmode',
		'checked' => ($fightmode=='attack'? true: false)
		)
	); ?>
<? } ?>	
<br/>

<div id='defendhelper' class="fightmodehelper" style='display:none'>
<?= kohana::lang('structures_battlefield.defendmodehelper'); ?>
</div>

<div id='attackhelper' class="fightmodehelper" style='display:none'>
<?= kohana::lang('structures_battlefield.attackmodehelper'); ?>
</div>

<div class='center'>
<?= form::submit(
	array(
		'name' => 'configurefightmode',
		'value' => kohana::lang('global.save'),		
		'class' => 'button button-small center'
	)
); 
?>
</div>

</fieldset>

<br style='clear:both'/>


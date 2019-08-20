<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>

<?php
$helper = "structures_" . $structure -> structure_type -> supertype . ".rest_helper" ;
?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<div id='helperwithpic'>
	<div id='locationpic'>
	<?php echo html::image('media/images/template/locations/rest_' . $structure -> structure_type -> supertype . '.jpg' ) ?>
	</div>
	<div id='helper'>
		<div id='helpertext'>
		<?php echo kohana::lang( $helper );?>
		</div>
		
		<div id='wikisection'>
			<?php echo html::anchor( 
					'https://wiki.medieval-europe.eu/index.php?title=Rest_-_Resting',
					kohana::lang('global.wikisection'), 
					array( 'target' => 'new', 'class' => 'button' ) ) 
			?>
		</div>	
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<? if ($character -> energy == 50 ) { ?>
<p class='center'>
	<?= kohana::lang('ca_rest.noneedtorest'); ?>
</p>
<? }
else
{
?>
<p>
<?php 

	$time = Utility_Model::secs2hms( $info['timeforfullenergy'] ) ;
	echo kohana::lang( "structures.rest_factor", 
		round($info['restfactor']/50*100,2), 
		$time[3] );
	echo "<br/>";
	
	echo form::open('structure/rest');
	echo form::hidden('structure_id', $structure -> id );
	echo "<div class='center'>";
	echo form::submit( array (
		'id' => 'submit', 
		'class' => 'button button-medium',
		'value' => kohana::lang('global.rest')));	
	echo '</div>';
	echo form::close();

?>
</p>
<? } ?>
<br style="clear:both;" />

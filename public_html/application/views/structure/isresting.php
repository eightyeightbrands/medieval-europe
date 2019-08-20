<div class="pagetitle"><?php echo kohana::lang("structures.rest_pagetitle");?></div>

<?php echo $submenu ?>

<?php 

$action = Character_Model::get_currentpendingaction( $character );

if ( $action != 'NOACTION' and $action['action'] != 'resttavern' )
{
	if ( $action['action'] == 'rest' )
		if ( $action['param2'] == true )
			$helper = "structures" . ".cartrest_helper" ;
		else
			$helper = "structures_" . $structure -> structure_type -> supertype . ".rest_helper" ;
}
else
	$helper = "structures_" . $structure -> structure_type -> supertype . ".rest_helper" ;
?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<div id='helperwithpic'>
<div id='locationpic'>
<?php 
if ( $action['param2'] == true )
	echo html::image('media/images/template/locations/rest_cart' . '.jpg' );
else
	echo html::image('media/images/template/locations/rest_' . $structure -> structure_type -> supertype . '.jpg' );
?>
</div>

<div id='helper'>
<?php echo kohana::lang( $helper );?>
</div>
<div style='clear:both'></div>
</div>

<br/>

<p class='center'>
<?php 
$restfactor = $action['param1'];
echo kohana::lang('structures.resting',  round($restfactor / 50 * 100,2 ) ); ?>	
</p>	

<br style="clear:both;" />

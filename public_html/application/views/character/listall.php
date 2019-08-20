<head>
<script type="text/javascript">

$(document).ready(function() { 
	$('#reset').click(
		function() {
			$('#name').val('');
			$('#online').attr('checked', false);
			return false;
		});
 });

</script>
</head>

<div id="tooltip">&nbsp;</div>

<div class="pagetitle"><?php echo kohana::lang('character.listall') ?></div>

<?php
echo form::open('/character/listall', array('method' => 'get' ) );
echo form::label( kohana::lang('global.name')) . '&nbsp;' . form::input( array( 'id' => 'name', 'name' => 'name', 'style' => 'width:200px') );
echo '&nbsp;&nbsp;';
echo form::label( kohana::lang('global.online')) . '&nbsp;' . form::checkbox('online', true );
echo "<div style='float:right'>";
echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'value' => kohana::lang('global.search')) );
echo form::submit( array( 'id' => 'reset', 'class' => 'button button-small', 'value' => kohana::lang('global.reset') ) );
echo "</div>";
echo form::close();
echo '<br/>';
echo '<b>' . $criteria .'</b>';
?>

<?php echo $pagination->render(); ?>

<br/>

<table class='smallfonts' border=0>
<th width='7%' class='center' ></th>
<th width='30%' style='vertical-align:top'>
	<?php 
	echo kohana::lang('global.name')
	.'&nbsp;'.
	html::anchor('character/listall/?orderby=c.name:asc', html::image('media/images/other/up.png'))
	.'&nbsp;'.
	html::anchor('character/listall/?orderby=c.name:desc', html::image('media/images/other/down.png'));
	?>
</th>

<th width='20%' style='vertical-align:top'><?php echo kohana::lang('global.role') ?></th>
<th width='15%' class='center' style='vertical-align:top'>
<?php 
echo kohana::lang('global.lastlogin') 
	.'&nbsp;'.
	html::anchor('character/listall/?orderby=u.last_login:asc', html::image('media/images/other/up.png'))
	.'&nbsp;'.
	html::anchor('character/listall/?orderby=u.last_login:desc', html::image('media/images/other/down.png'));	
?></th>
<th width='5%' style='vertical-align:top'><?php echo kohana::lang('global.status'); ?></th>
<th width='5%' style='vertical-align:top'><?php echo kohana::lang('global.meditating'); ?></th>
<?php
$r=0;
foreach ( $characters as $c )
{		
	$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1' ;
	
	$char = ORM::factory('character', $c -> id );
	
	echo "<tr class='$class'>";  
	
	echo "<td>" ;	

	 echo html::image('media/images/heraldry/'.$c->kingdom_image.'-small.png',	array( 'class' => 'size15',
			'title' => kohana::lang($c->kingdom_name ) ) );
	
	echo html::image('media/images/badges/religionsymbols/symbol_'.$c->church_name.'.png',	array( 'class' => 'size15',
			'style' => 'margin-left:2px', 
			'title' => kohana::lang( 'religion.church-' . $c->church_name ) ) );	
	
	echo '</td>';
	echo "<td>";
	
	$title =  Character_Model::get_basicpackagetitle( $c -> id );
	if (!empty($title))
		echo kohana::lang($title) . " " ;
	echo Character_Model::create_publicprofilelink( $c -> id, $c -> character_name ) . "</td>";
	
	//$role = $char->get_current_role();
	//$role = null;
	//if ( !is_null( $role ) )
		echo "<td class='sinistra'>". $char -> get_rolename( true )."</td>";
	//else
	//		echo "<td class='center'>&nbsp;</td>";	
	
	echo "<td class='center'>". $c -> last_login."</td>";
	
	$online = Character_Model::is_online($c-> id);	
	
	if ( $online )
		echo "<td class='sinistra' style='color:#009933;font-weight:bold'>Online</td>";
	else
		echo "<td class='sinistra'>&nbsp;</td>";
	
	echo "<td class='center'>";
	if ( Character_Model::is_meditating( $c -> id ) )
		echo kohana::lang('global.yes');
	else
		echo kohana::lang('global.no');
		
	echo "</td>";
	echo "</tr>";
	$r++;
}
?>
</table>
<br/>
<?php echo $pagination->render(); ?>

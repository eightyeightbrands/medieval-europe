<div class="pagetitle"><?php echo kohana::lang('character.role_pagetitle') ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5><?php echo Kohana::lang('character.current_role')?></h5>

<?php 
if ( !$role ) {
?>
<p class='center'><?= kohana::lang('character.norole'); ?></p>
<?
}
else
{
?>
	<div style='float:left'>	
		<?= kohana::lang('character.current_role') .": <span class='value'>". $char -> get_rolename( true ) ."</span>"; ?>
		<br/>
		<?= kohana::lang('global.kingdom') . ":<span class='value'> " . kohana::lang($role->region->kingdom -> get_name() ) . "</span>";?>
		<br/>
		<?= kohana::lang('character.nominatedon') . ":<span class='value'> " . Utility_Model::format_date($role->begin) . "</span><br/>"; ?>
	</div>
	
	<div style='float:right'>
		<?= html::anchor( 
		'structure/resign_from_role', 		
		kohana::lang('character.resign'),
		array( 
			'class' => 'button',
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
		); 
	?>
	</div>
	
	<br style='clear:both'/>	
<?php 
}
?>

<table style='width:100%;font-size:11px; margin:15px 0;'>
	<?php
	$i=0;
	foreach ( $rptitles as $r )
	{	
		echo '<tr>';
		echo '<td width=60px>'.$r->get_title_image(array('style'=>'margin:5px 0; width:50px')).'</td>';
		echo '<td>';
		echo '<div style="font-size:13px">'. $r -> get_title( true ).'</div>';
		echo '</td>';
		echo '</tr>';
	}
	?>
</table>

<h5><?php echo Kohana::lang('character.roleshistory')?></h5>
<br/>
<?php
if ($roleshistory->count() == 0 )
{
?>
<p class='center'> <?= kohana::lang('character.noroleshistory'); ?> </p>
<?
}
else
{
?>
<table>
<th width="30%"><?php echo kohana::lang('global.role') ?></th>
<th width="20%"><?php echo kohana::lang('global.kingdom') ?></th>
<th width="15%"><?php echo kohana::lang('global.place') ?></th>
<th width="10%" class="center"><?php echo kohana::lang('global.begin') ?></th>
<th width="10%" class="center"><?php echo kohana::lang('global.end') ?></th>
<th width="10%" class="center"><?php echo kohana::lang('global.days') ?></th>
<?php
$i=0;
foreach ( $roleshistory as $r )
{	
	($i % 2 == 0) ? $class = 'alternaterow_1' : $class = 'alternaterow_2';
	echo "<tr class=\"$class\">";
	echo '<td>' . $r -> get_title( true ) . '</td>';
	echo "<td>" . kohana::lang( $r-> region -> kingdom -> get_name( $r -> begin ) )."</td>";
	echo "<td>" . kohana::lang( $r-> region -> name)."</td>";
	echo "<td class='center'>".Utility_Model::format_date($r->begin)."</td>";
	echo "<td class='center'>".( is_null( $r->end ) ? "" : Utility_Model::format_date($r->end) )."</td>";
	echo "<td class='center'>".( is_null( $r->end ) ? intval( (time()-$r->begin) / ( 3600*24) ) : intval( ($r->end-$r->begin) / ( 3600*24) ) )."</td>";
	echo "</tr>";
	$i++;
}
?>
</table>
<?php } ?>
<br style="clear:both;" />
<head>
<script>
$(document).ready(function()
{	
	$("#group_charname").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('groups.title_view_groups') ?></div>

<?php echo $submenu ?>


<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?= $secondarymenu; ?>
</div>


<br/>
<div>
	<div style="float:left;margin-right:5px">
	<?
	$file = "media/images/groups/". $group -> id. ".png";	
	if ( file_exists( $file) )
		echo html::image(
		$file);
	else
		echo html::image(
		'media/images/template/group_no_image.png');
	?>
	</div>
	<div style="float:left">
	<h2><?= $group -> name; ?></h2>
	<?= $group -> description; ?>
	<br/>
	<?= kohana::lang('groups.group_founder') .'&nbsp;' . Character_Model::create_publicprofilelink( $group -> character_id, null ); ?>
	<br/>
	<?= kohana::lang('global.type') . ': ' . kohana::lang( $group -> type); ?>
	</div>
</div>

<br style='clear:both'/>

<br/>

<? if ($character -> id == $group -> character_id )
{
?>

<fieldset>
<legend><?= kohana::lang('groups.invitechars')?></legend>
<?
	echo form::open('group/view/'.$group->id);
	echo form::input( array( 
		'id' =>'group_charname', 
		'name' => 'group_charname', 
		'value' =>  $form['group_charname']));
		
	if (!empty ($errors['group_charname'])) 
		echo "<div class='error_msg'>".$errors['group_charname']."</div>";
		
	echo form::submit( array (
		'id' => 'submit', 
		'class' => 'submit', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.add'));		
	echo "<br/><br/>";
	if (count($pendents)	> 0 ) { 

		echo "<h4 class='center'>" . kohana::lang('groups.list_pendents') . "</h4>";
		
		foreach ( $pendents as $pendent )
		{
			
			echo "<div class='center'> " . Character_Model::create_publicprofilelink( $pendent -> character_id, null ) . "</div>";		
		}
		
	}	
?>	
</fieldset>
<br/>
<?
}
?>

<fieldset>
<legend>
<?= kohana::lang('groups.list_of_members'); ?>
</legend>
<div class="pagination"><?php echo $pagination->render('extended'); ?></div>
<table class="small">
<th width='20%'>
<?= kohana::lang('global.name'); ?>
</th>
<th width='15%'>
<?= kohana::lang('global.age'); ?>
</th>
<? 
if ( $istutor and $group -> classification == 'tutor' ) { ?>
<th width='10%'>
<?= kohana::lang('global.lastlogin'); ?>
</th>
<th width='10%'>
<?= kohana::lang('character.health'); ?>
</th>
<th width='15%'>
<?= kohana::lang('character.create_spokenlanguages'); ?>
</th>
<? } ?>
<th width='15%'>
<?= kohana::lang('global.kingdom'); ?>
</th>
<th>
</th>
<?
	$k = 0;
	foreach ( $members as $member )
	{
		$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : '' ;
?>
<tr class="<?= $class; ?>">

<td class="center">
<?= Character_Model::create_publicprofilelink( $member -> character_id, null ); ?>
</td>
<td class="center">
<?= 
Utility_Model::secs2hmstostring( 
	Character_Model::get_age_s( 
		$member -> character_id, 'year'));
 ?>
</td>
<? if ( $istutor and $group -> classification == 'tutor' )
	{ 
?>

<td class="center">
<?= Utility_Model::time_elapsed_string($member -> character -> user -> last_login );?>
</td>
<td class='center'>
	<?= $member -> character -> health . "/100"; ?>
</td>
<td class='center' style='word-wrap: break-word;'>
<? 
	foreach ($member -> character -> user -> user_languages as $language)
	{
		
		if (!empty($language -> language))
		{
			if ($language -> position == 1)
				echo "<b>" . $language -> language . "</b>";
			else
				echo $language -> language;
			echo "<br/>";
		}
	}
?>
</td>
<? } ?>
<td class='center'>
<?= kohana::lang($member -> character -> region -> kingdom -> name); ?>
</td>
<td class="center">
<?				
	if ( $group -> character_id == $character -> id )
	{
		echo html::anchor(
		'/group/remove/'. $member -> group_id . '/' . $member -> character_id
		, kohana::lang('groups.remove'),
			array ( 
				'class' => 'command',
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
	}
	else
		echo "-";
?>	
</td>
</tr>
<?
		$k++;
	}
?>
</table>	

<div class="pagination"><?php echo $pagination->render('extended'); ?></div>
</fieldset>
<br style="clear:both;" />

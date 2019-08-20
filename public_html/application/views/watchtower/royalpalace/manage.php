<?php echo html::script('media/js/jquery/plugins/markitup/jquery.markitup.js', FALSE)?>
<?php echo html::script('media/js/jquery/plugins/markitup/sets/bbcode/set.js', FALSE)?>


<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php $info = $structure -> region -> kingdom -> get_info(); ?>	

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<fieldset>
<legend><?php echo kohana::lang('character.general_stats')?></legend>
<?php
echo kohana::lang('structures_royalpalace.totalcitizens', count( $info['citizens']) ). '<br/>';
echo kohana::lang('structures_royalpalace.averageage', $info['averageage'] ). '<br/>';
foreach ( $info['religion'] as $key => $value )
	foreach ( $value as $key2 => $value2 )
		echo kohana::lang('structures_royalpalace.religionfollowersreligion', 
			kohana::lang('religion.religion-' . $key), 
			kohana::lang('religion.church-' . $key2), $value2['total'], $value2['percentage'] ) . '<br/>';
?>
</fieldset>
<br/>
<?= $section_setstructurename; ?>
<br/>
<?= $section_description; ?>
<br/>
<?= $section_informativemessage; ?>
<br/>
<?= $section_loadpicture; ?>
<br style="clear:both;" />
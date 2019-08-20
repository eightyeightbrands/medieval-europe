<div class="pagetitle"><?php echo kohana::lang('user.edit_pagetitle') ?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('user.edit_helper')?></div>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<?php echo form::open() ?>
<fieldset>
<legend><?php echo kohana::lang('global.general')?></legend>
<table>
<tr>
	<td class="right" width='40%'><?php echo kohana::lang('global.nationality')?></td>
	<td><?php echo form::dropdown('nationality', $countrycodes, $user -> nationality);?></td>
</tr>
<tr>
	<td class="right" ><?php echo kohana::lang('user.register_hidemaxstatsbadges')?></td>
	<td><?php echo form::checkbox( 'hidemaxstatsbadges', 'activate', ($user -> hidemaxstatsbadges == 'Y' ? true : false ) ) ?></td>
</tr>
<tr>
	<td class="right" ><?php echo kohana::lang('user.register_availableregfunctions')?></td>
	<td><?php echo form::checkbox( 'availableregfunctions', 'available', ($user -> availableregfunctions == 'Y' ? true : false ) ) ?></td>
</tr>
<? if (Auth::instance() -> logged_in('admin') or Auth::instance()->logged_in('tester')) { ?>
<tr>
<td class="right">
Choose Skin:
</td>
<td>
<?= form::dropdown('skin', array( 'classic' => 'Classic', 'new' => 'New') ); ?>
</td>
</tr>
<? } ?>
<tr>
<td width='40%' class="right" >
<?= kohana::lang('character.create_spokenlanguage1');?> 
</td>
<td>
<?php echo form::dropdown(
	array(
		'name' => 'spokenlanguage1'),
	$spokenlanguages,
	(isset($languages[1])? $languages[1] : '' )
	); 
?>
</td>
</tr>


<tr>
<td width='40%' class="right" >
<?= kohana::lang('character.create_spokenlanguage2');?> 
</td>
<td>
<?php echo form::dropdown(
	array(
		'name' => 'spokenlanguage2'),
	$spokenlanguages,
	(isset($languages[2])? $languages[2] : '' )
	); 
?>
</td>
</tr>


<tr>
<td width='40%' class="right" >
<?= kohana::lang('character.create_spokenlanguage3');?> 
</td>
<td>
<?php echo form::dropdown(
	array(
		'name' => 'spokenlanguage3'),
	$spokenlanguages,
	(isset($languages[3])? $languages[3] : '' )
	); 
?>
</td>
</tr>


<tr>
<td width='40%' class="right" >
<?= kohana::lang('character.create_spokenlanguage4');?> 
</td>
<td>
<?php echo form::dropdown(
	array(
		'name' => 'spokenlanguage4'),
	$spokenlanguages,
	(isset($languages[4])? $languages[4] : '' )
	); 
?>
</td>
</tr>


<tr>
<td width='40%' class="right" >
<?= kohana::lang('character.create_spokenlanguage5');?> 
</td>
<td>
<?php echo form::dropdown(
	array(
		'name' => 'spokenlanguage5'),
	$spokenlanguages,
	(isset($languages[5])? $languages[5] : '' )
	); 
?>
</td>
</tr>


<tr>
<td width='40%' class="right" >
<?= kohana::lang('user.showlanguageinspublicprofile'); ?>
</td>
<td>
<?php echo form::checkbox( 'showlanguagesinpublicprofile', 'show', ($user -> showlanguages == 'Y' ? true : false ) ) ?>
</td>
</tr>

<tr>
<td colspan='2' class='center'>
<?php 
	echo form::submit( array (
		'id' => 'submit', 
		'name' => 'general',
		'class' => 'button button-medium', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
</td>
</tr>

</table>
</fieldset>
<?= form::close(); ?>
<br/>

<!-- email related -->

<?php echo form::open() ?>
<fieldset>
<legend>Email</legend>
<table>
<tr>
	<td class="right" ><?php echo kohana::lang('user.register_newsletter')?></td>
	<td><?php echo form::checkbox( 'newsletter', 'send', ($user -> newsletter == 'Y' ? true : false ) ) ?></td>
</tr>
<tr>
	<td class="right" width='40%'><?php echo kohana::lang('user.receiveingamemessagesonemail')?></td>
	<td><?php echo form::checkbox( 'receiveigmessagesonemail', 'receive', ($user -> receiveigmessagesonemail == 'Y' ? true : false ) ) ?></td>
</tr>
<td colspan='2' class='center'>
<?php 
	echo form::submit( array (
		'id' => 'submit', 
		'name' => 'emailsection',
		'class' => 'button button-medium', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
</td>
</tr>
</table>
</fieldset>
<?= form::close(); ?>

<br/>
<!-- automated sleep -->

<?php if ( Character_Model::get_premiumbonus( $char -> id, 'automatedsleep' ) !== false ) { ?>
<?php echo form::open() ?>
<fieldset>
<legend><?php echo kohana::lang('bonus.automatedsleep_name')?></legend>
<table>
<tr>
	<td  class="right" width='40%'><?php echo kohana::lang('user.register_disablesleepafteraction')?></td>
	<td><?php echo form::checkbox( 'disablesleepafteraction', 'activate', ($user -> disablesleepafteraction == 'Y' ? true : false ) ) ?></td>
</tr>
<tr>
	<td  class="right" width='40%'><?php echo kohana::lang('user.register_maxglut')?></td>
	<td><?php echo form::input( array('name' => 'maxglut', 'value' => $user -> maxglut, 'class' => 'input-xsmall') ); ?></td>
</tr>
<tr>
<td colspan='2' class='center'>
<?php 
	echo form::submit( array (
		'id' => 'submit', 
		'name' => 'automatedsleep',
		'class' => 'button button-medium', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
</td>
</tr>
</table>
</fieldset>
<?= form::close(); ?>
<?php } ?>

<!-- basic package -->

<?php if ( Character_Model::get_premiumbonus( $char -> id, 'basicpackage' ) !== false ) { ?>
<?php echo form::open() ?>
<fieldset>
<legend><?php echo kohana::lang('bonus.basicpackage_name')?></legend>
<table>
<tr>
	
	<td	 class="right" width='40%'><?php echo kohana::lang('global.title')?></td>
	<td><?php echo form::dropdown('title', $titles, $title) ?></td>

</tr>
<tr>
<td colspan='2' class='center'>
<?php 
	echo form::submit( array (
		'id' => 'submit', 
		'name' => 'basicpackage',
		'class' => 'button button-medium', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
</td>
</tr>
</table>
</fieldset>
<?= form::close(); ?>
<?php 
} 
?>

<br style="clear:both">

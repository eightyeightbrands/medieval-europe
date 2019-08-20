<div class="pagetitle"><?php echo kohana::lang('character.accessrpforum_pagetitle') ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>
<p>
<?php echo kohana::lang('character.accessrpforumhelper',
$data['username'], $data['password']); ?>
</p>

<br/>

<div class='center'>
<?php echo html::anchor(kohana::config('medeur.officialrpforumurl'), kohana::lang('global.gotoforum'), 
	array(
		'class' => 'button button-medium',
		'target' => 'new'
		)); ?>&nbsp;
<?php echo html::anchor(kohana::config('medeur.officialrpforumurl').'/index.php?action=reminder', 
	kohana::lang('global.resendpassword'), 
	array(
		'class' => 'button button-medium',
		'target' => 'new')); ?>
</div>

<br style="clear:both;" />

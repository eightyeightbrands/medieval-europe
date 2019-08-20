<div class="pagetitle"><?php echo Kohana::lang("character.resign_from_role_titlepage")?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id="helper"><?php echo Kohana::lang("character.resign_from_role_helper")?></div>

<p>
<?php echo form::open(url::current()); ?>
<?php echo form::label('reason', Kohana::lang('global.reason'));?>
<br/>
<?php echo form::textarea( array( 'name'=>'resignreason', 'rows' => 3, 'cols' => 90) ); ?>
<br/><br/>
<center>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-small', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.resign')) ;
?>
</center>
<?php echo form::close(); ?>
</p>

<br style='clear:both'/>



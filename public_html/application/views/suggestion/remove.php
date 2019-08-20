
<div class="pagetitle"><?php echo kohana::lang('suggestions.remove')?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<div class='center'>
<? $status = $this->uri->segment(3);?>
<? if ($status == 'new') $class='button selected'; else	$class='button'; ?>

<?php echo html::anchor('suggestion/index/new', kohana::lang('suggestions.new'), array( 'class' => $class ) ); ?>

<? if ($status == 'fundable') $class='button selected'; else $class='button'; ?>

<?php echo html::anchor('suggestion/index/fundable', kohana::lang('suggestions.fundable'), array( 'class' => $class ) ); ?>

<? if ($status == 'funded') $class='button selected'; else	$class='button'; ?>	
<?php echo html::anchor('suggestion/index/funded', kohana::lang('suggestions.funded'), array( 'class' => $class ) ); ?>

<? $class='button selected';?>
<?php echo html::anchor('suggestion/add/', kohana::lang('suggestions.addmodify'),	array( 'class' => $class ) ); ?>
		
</div>	

<br/>

<fieldset class='alternaterow_1'>
<?php echo form::open(); ?>

<?php echo form::hidden('id', $suggestion -> id) ?>
<br/>

<?php echo form::label( array( 'class' => 'form', 'name' => 'reason' ), kohana::lang('global.reason') ); ?>
<br/>
<?php	echo form::textarea( array( 'id' => 'reason', 'name' => 'reason', 'value' => $form['reason'], 'rows' => 5, 'cols' => 90, 'style' => 'overflow-y: scroll') ); ?>
<?php if (!empty ($errors['reason'])) echo "<div class='error_msg'>".$errors['reason']."</div>";?>

<div class='center'>
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.delete')));?>
</div>
<?php echo form::close() ?>
</fieldset>

<br style='clear:both'/>

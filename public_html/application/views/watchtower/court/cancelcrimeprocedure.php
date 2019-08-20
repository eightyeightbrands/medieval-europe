<div class="pagetitle"><?php echo kohana::lang('structures_court.cancelcrimeprocedure_pagetitle')?></div>


<?php
echo $submenu
?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('structures_court.cancelcrimeprocedure_helper')?>
</div>

<br/>

<?php echo form::open() ?>
<?php echo form::hidden( 'structure_id', $structure -> id ) ?>
<?php echo form::hidden( 'crimeprocedure_id', $crimeprocedure -> id ) ?>


<p>
<?php echo Kohana::lang('structures_court.target');?>:&nbsp;
<span class='value'><?php echo $target -> name ?></span>
</p>

<p>
<?php echo Kohana::lang('structures_court.crimesummary');?>:&nbsp;
<span class='value'><?php echo $crimeprocedure -> text ?></span>
</p>

<p>
<?php echo Kohana::lang('structures_court.cancelreason') ?>
<br/>
<?php echo form::textarea( array( 'id' => 'cancelreason', 'name' => 'cancelreason', 'value' => $form['cancelreason'], 'cols' => 90, 'rows' => 5 ) );?>
</p>

<p class='center'>
<?php echo form::submit(array ('id'=>'submit', 'class' => 'button button-medium', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'name'=> 'create', 'value'=> kohana::lang('global.delete'))) ?>
</p>

<?php echo form::close(); ?>

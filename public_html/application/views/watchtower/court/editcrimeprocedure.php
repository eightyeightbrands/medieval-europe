<div class="pagetitle"><?php echo kohana::lang('structures_court.editcrimeprocedure_pagetitle')?></div>


<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('structures_court.editcrimeprocedure_helper')?>
</div>

<?php echo form::open() ?>
<?php echo form::hidden( 'structure_id', $structure -> id ) ?>
<?php echo form::hidden( 'crimeprocedure_id', $crimeprocedure -> id ) ?>

<br/>

<p>
<?php echo form::label( array( 'for' => 'target', 'class'=> 'form' ), Kohana::lang('structures_court.target'));?>
<span class='value'><?php echo $target -> name ?></span>
</p>

<p>
<?php echo form::label( array( 'for' => 'summary', 'class'=> 'form' ), Kohana::lang('structures_court.crimesummary'));?>
<br/>
<?php echo form::textarea( array( 'id'=>'reason', 'name' => 'summary', 'value' => $form['summary'], 'cols' => 60, 'rows' => 5 ) );?>
</p>

<p>
<?php echo form::label( array( 'for' => 'reason', 'class'=> 'form' ), Kohana::lang('structures_court.trialurl'));?>
<?php echo form::input( array( 'id'=>'trialurl', 'name' => 'trialurl', 'value' =>  ($form['trialurl']), 'style' => 'width:320px') );?>
</p>

<p class='center'>
<?php echo form::submit(array ('id'=>'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'name'=> 'create', 'value'=> kohana::lang('global.edit'))) ?>
</p>

<?php echo form::close(); ?>

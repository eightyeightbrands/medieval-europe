<head>
	
  <script>
	
	$(document).ready(function()
	{	
		$("#target").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});
  });
  </script>
</head>
	
<div class="pagetitle"><?php echo kohana::lang('structures_court.opencrimeprocedure_pagetitle')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_court.opencrimeprocedure_helper')?></div>

<br/>


<?php echo form::open() ?>
<?php echo form::hidden( 'structure_id', $structure -> id ) ?>

<p>
<?php echo form::label( array( 'for' => 'target', 'class'=> 'form' ), Kohana::lang('structures_court.target'));?>
<?php echo form::input( array( 'id' => 'target', 'name' => 'target', 'value' =>  ($form['target']), 'style' => 'width:200px') );?>
</p>

<p>
<?php echo form::label( array( 'for' => 'summary', 'class'=> 'form' ), Kohana::lang('structures_court.crimesummary'));?>
<?php echo form::textarea( array( 'id'=>'summary', 'name' => 'summary', 'value' => $form['summary'], 'cols' => 60, 'rows' => 5) );?>
</p>

<p>
<?php echo form::label( array( 'for' => 'trialurl', 'class'=> 'form' ), Kohana::lang('structures_court.trialurl'));?>
<?php echo form::input( array( 'id'=>'trialurl', 'name' => 'trialurl', 'value' =>  ($form['trialurl']), 'style' => 'width:320px') );?>
</p>

<p class='center'>
<?php echo form::submit(array ('for'=>'submit', 'class' => 'button button-medium', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'name'=> 'create', 'value'=> kohana::lang('global.open'))) ?>
</p>

<?php echo form::close(); ?>

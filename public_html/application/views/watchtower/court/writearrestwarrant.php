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
	
<div class="pagetitle"><?php echo kohana::lang('structures_court.writearrestwarrant_pagetitle')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div id='helper'>
<?php echo kohana::lang('structures_court.writearrestwarrant_helper')?>
</div>

<br/>

<?php echo form::open() ?>
<?php echo form::hidden( 'structure_id', $structure -> id ) ?>


<p>
<?php echo form::label('target', Kohana::lang('structures_court.target'));?>&nbsp;
<?php echo form::input( array( 'id'=>'target', 'name' => 'target', 'value' =>  ($form['target']), 'style' => 'width:352px') );?>
</p>

<p>
<?php echo form::label('reason', Kohana::lang('structures_court.warranttext'));?><br/>
<?php echo form::textarea( array( 'id'=>'reason', 'name' => 'reason', 'value' => $form['reason'], 'cols' => 90, 'rows' => 5 ) );?>
</p>

<p>
<?php echo form::label('reason', Kohana::lang('structures_court.triallink'));?>&nbsp;
<?php echo form::input( array( 'id'=>'triallink', 'name' => 'triallink', 'value' =>  ($form['triallink']), 'style' => 'width:320px') );?>
</p>

<p class='center'>
<?php echo form::submit(array ('id'=>'submit', 'class' => 'button button-small', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'name'=> 'create', 'value'=> kohana::lang('global.create'))) ?>
</p>

<?php echo form::close(); ?>

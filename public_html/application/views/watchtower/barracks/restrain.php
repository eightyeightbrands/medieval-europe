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

<div class="pagetitle"><?php echo kohana::lang('structures_actions.barracks_restrain')?></div>

<?= $submenu; ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_barracks.restrain_helper')?></div>

<br/>

<p>
<?php 
	echo form::open('/barracks/restrain');
	echo form::label('hours', Kohana::lang('global.hours'))  . '&nbsp; ';
	echo form::hidden('structure_id', $structure -> id);
	echo form::input(  array( 'id' => 'hours', 'name' => 'hours', 'size' => 2, 'maxlength' => 3, 'style' => 'text-align:right' , 'value' => $form['hours']) ) ;
?>
</p>

<p>
	<?php echo form::label('target', Kohana::lang('structures_barracks.target'));?>&nbsp;
	<?php echo form::input( array( 'id'=>'target', 'name' => 'target', 'value' =>  $form['target'], 'style' => 'width:348px') );?>
</p>

<p>
<?php	echo form::label( kohana::lang( 'global.reason') ); ?>
<br/>
<?php	echo form::textarea(  array( 'id' => 'reason', 'name' => 'reason', 'rows' => 5, 'cols' => 80, 'value' => $form['reason'] ) ) ; ?>
</p>

<p class='center'>
<?php 
	echo form::submit(
		array ('id'=>'submit', 'class' => 'button button-medium', 'onclick' => 'return confirm(\''.
			kohana::lang('global.confirm_operation').'\')', 'name'=>'submit', 'value'=> kohana::lang('global.confirm')))."</td>";
	echo form::close();
?>
</p>

<br style="clear:both;" />

<div class="pagetitle"><?php echo kohana::lang('structures_court.imprison_pagetitle')?></div>


<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper' >
  <div id='helpertext'>
		<?php echo kohana::lang('structures_court.imprison_helper')?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_IGJustice#Imprisoning_a_criminal',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<p>
<?php 
	echo form::open('court/imprison');
	echo form::hidden('crimeprocedure_id', $crimeprocedure -> id ); 
	echo form::hidden('structure_id', $structure -> id ); 
	echo kohana::lang('structures_court.imprison_text', $crimeprocedure -> character -> name ); 	
	echo "&nbsp;&nbsp;";
	echo form::input(  array( 'id' => 'hours', 'name' => 'hours', 'size' => 2, 'maxlength' => 3, 'style' => 'text-align:right' , 'value' => $form['hours']) ) ;
	echo "<br/>";
	echo kohana::lang('structures_court.imprison_text2', $crimeprocedure -> character -> name ); 	
	echo form::dropdown('prison', $combo_prisons, $form['prison']);
	echo "<br/>";
	echo "<br/>";
	echo '<center>';
	echo form::submit(
		array ('id'=>'submit', 'class' => 'button button-medium', 'onclick' => 'return confirm(\''.
			kohana::lang('global.confirm_operation').'\')', 'name'=>'submit', 'value'=> kohana::lang('structures_court.imprison')))."</td>";
	echo '<center>';		
	echo form::close();
?>
</p>


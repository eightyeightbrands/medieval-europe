<div class="pagetitle"><?php echo kohana::lang('religion.pray_pagetitle') ?></div>

<br/>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('religion.pray_helper') ?>
</div>

<br/>
<?php
		echo form::open();	
		echo kohana::lang('religion.pray_text');
		echo form::hidden('structure_id', $structure -> id ); 
		echo form::input(array( 'id'=> 'hours', 'name' => 'hours', 'style' => 'width:10px;maxlenght:1;text-align:right' ) );		
		echo "<span style='margin-left:10px'>" .  form::submit( array (
		'id' => 'submit', 
		'class' => 'submit', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('religion.pray')) .
		'</span>';
		echo form::close();
?>

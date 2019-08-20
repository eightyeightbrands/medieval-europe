<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.royalpalace_throneroom_pagetitle') ?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<br/>

<div>
<?php 
if ( $structure -> character_id > 0 )
	echo kohana::lang('structures_royalpalace.tr_text_novacant', $structure-> region->get_charinrole('king')->name );
else
{	
	echo kohana::lang('structures_royalpalace.tr_text_vacant', 
			$structure -> region -> kingdom -> regions -> count(),  
			implode ( ", ", $regionnames), $structure -> region -> kingdom -> get_regent_cost() );
			
	echo '<br/><br/>';
	
	echo form::open('royalpalace/become_king/', array('class'=>'becomeking_form'));
	echo form::hidden( 'structure_id', $structure->id );
?>
	<div class='center'>
		<?php
			echo form::submit(	array ('id'=>'submit', 'class' => 'submit', 'name'=>'becomeking', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value'=> kohana::lang('structures_royalpalace.becomeking')));
		?>
	</div>	

<?php 
	echo form::close();
}
?>
</div>

<div style='clear:both'></div>


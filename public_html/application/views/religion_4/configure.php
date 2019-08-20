<div class="pagetitle"><?php echo kohana::lang('structures.' . $structure -> structure_type -> type . '_' . 
	$structure -> structure_type -> church -> name )  ?></div>

<?php echo $submenu ?>

<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/' . $structure -> structure_type -> type . 
'_' . $structure -> structure_type -> church -> name . '.jpg' ); ?>
</div>
<?php form::open() ?>
<?php echo form::label('description', Kohana::lang('character.description'));?>
<?php echo form::textarea( array( 'name'=>'description', 'value' => $form['description'], 'rows' => 5, 'cols' => 90) ); ?>
<?php echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
?>
<?php echo form::close() ?>
<hr/>

<p>

<br/>
<?php echo kohana::lang('religion.faithpoints') . ': <b>' .  $structure -> fp . '</b>' ; ?>
<br/>

<?php echo kohana::lang('religion.faithpoints') . ': <b>' ;
if ( ! Structure_Model::get_stat_d( $structure -> id, 'faithpoints' ) -> loaded )
	echo 0 ;
else
	echo Structure_Model::get_stat_d( $structure -> id, 'faithpoints' ) -> value ; 
echo  '</b>';?>
<br/>
<?php 
echo kohana::lang('religion.accumulatedfaithpoints') . ': <b>' ; 
if ( ! Structure_Model::get_stat_d( $structure -> id, 'fpcontribution' ) -> loaded )
	echo 0 ;
else
	echo Structure_Model::get_stat_d( $structure -> id, 'fpcontribution' ) -> value ; 
echo  '</b>'; ?>
<br/>
<?php echo kohana::lang('religion.followers') . ': <b>' .  $info['followers'] . '</b>' ; ?>
<br/>
<?php echo kohana::lang('religion.percentage') . ': <b>' .  $info['percentage'] . '%</b>' ; ?>
</p>
</div>
<br/>

<br style="clear:both;" />

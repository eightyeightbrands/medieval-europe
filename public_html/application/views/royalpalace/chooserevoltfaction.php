<div id="pagetitle">
<?php echo kohana::lang('structures_actions.royalpalace_chooserevoltfaction') ?>
</div>

<div id="submenu">
<ul>
<li><?php echo html::anchor('region/view', kohana::lang('menu_logged.position'))?> </li>
</ul>
</div>

<div class="separator">&nbsp;</div>

<p>
<?php echo kohana::lang('structures_royalpalace.revolt_par1' ); ?>
</p>

<p>
<?php 
if ( count($revolters) == 0 ) 
	echo '<i>' . kohana::lang('structures_royalpalace.revolt_par2_noone') . '</i>' ;
else
{
	echo kohana::lang('structures_royalpalace.revolt_par2');	
	echo  implode ( ', ', $revolters) ;
}	
	
	?>
</p>


<p>
<?php 
if ( count($supporters) == 0 ) 
	echo '<i>' . kohana::lang('structures_royalpalace.revolt_par3_noone') . '</i>' ;
else
{
	echo kohana::lang('structures_royalpalace.revolt_par3');	
	echo  implode ( ', ', $supporters) ;
}

echo '<br/></br>';
echo kohana::lang('structures_royalpalace.revolt_par4', Utility_Model::countdown( $battleround -> starttime) );
		
?> 

</p>
<br/><br/>
<?php
echo form::open('royalpalace/chooserevoltfaction');

echo form::submit( array( 'id' => 'supportking', 'name' => 'supportking', 'class' => 'button button-medium' , 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => kohana::lang('structures_actions.revolt_supportregent') )) ;
echo "&nbsp;&nbsp;";

echo form::submit( array( 'id' => 'supportrevolt',  'name' => 'supportrevolt', 'class' => 'button button-medium' , 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => kohana::lang('structures_actions.revolt_supportrevolt') )) ;
	
echo "&nbsp;&nbsp;";
echo form::submit( array( 'id' => 'leaverevolt',  'name' => 'leaverevolt', 'class' => 'button button-medium' , 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => kohana::lang('structures_actions.revolt_leaverevolt') )) ;

echo form::close();
?>

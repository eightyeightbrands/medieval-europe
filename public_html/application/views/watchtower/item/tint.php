<div class='pagetitle'><?php echo kohana::lang('charactions.select_color_tint') ?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang( 'charactions.tint_helper')?></div>

<table style="margin-top:15px">
<tr>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 66);
   echo form::hidden('green', 66);
   echo form::hidden('blue', 66);
   echo form::hidden('hexcolor', '#424242');
   echo '<div style="background-color:#424242; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 189);
   echo form::hidden('green', 189);
   echo form::hidden('blue', 189);
   echo form::hidden('hexcolor', '#bdbdbd');
   echo '<div style="background-color:#bdbdbd; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 230);
   echo form::hidden('green', 230);
   echo form::hidden('blue', 230);
   echo form::hidden('hexcolor', '#e6e6e6');
   echo '<div style="background-color:#e6e6e6; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 255);
   echo form::hidden('green', 255);
   echo form::hidden('blue', 255);
   echo form::hidden('hexcolor', '#ffffff');
   echo '<div style="background-color:#ffffff; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

</tr>

<tr>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 138);
   echo form::hidden('green', 8);
   echo form::hidden('blue', 8);
   echo form::hidden('hexcolor', '#8A0808');
   echo '<div style="background-color:#8A0808; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 180);
   echo form::hidden('green', 4);
   echo form::hidden('blue', 4);
   echo form::hidden('hexcolor', '#B40404');
   echo '<div style="background-color:#B40404; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 254);
   echo form::hidden('green', 46);
   echo form::hidden('blue', 46);
   echo form::hidden('hexcolor', '#FE2E2E');
   echo '<div style="background-color:#FE2E2E; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 245);
   echo form::hidden('green', 169);
   echo form::hidden('blue', 169);
   echo form::hidden('hexcolor', '#F5A9A9');
   echo '<div style="background-color:#F5A9A9; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

</tr>

<tr>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 97);
   echo form::hidden('green', 56);
   echo form::hidden('blue', 11);
   echo form::hidden('hexcolor', '#61380B');
   echo '<div style="background-color:#61380B; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 180);
   echo form::hidden('green', 95);
   echo form::hidden('blue', 4);
   echo form::hidden('hexcolor', '#B45F04');
   echo '<div style="background-color:#B45F04; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 255);
   echo form::hidden('green', 128);
   echo form::hidden('blue', 0);
   echo form::hidden('hexcolor', '#FF8000');
   echo '<div style="background-color:#FF8000; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 250);
   echo form::hidden('green', 172);
   echo form::hidden('blue', 88);
   echo form::hidden('hexcolor', '#FAAC58');
   echo '<div style="background-color:#FAAC58; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

</tr>

<tr>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 56);
   echo form::hidden('green', 97);
   echo form::hidden('blue', 11);
   echo form::hidden('hexcolor', '#38610B');
   echo '<div style="background-color:#38610B; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 75);
   echo form::hidden('green', 138);
   echo form::hidden('blue', 8);
   echo form::hidden('hexcolor', '#4B8A08');
   echo '<div style="background-color:#4B8A08; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 116);
   echo form::hidden('green', 223);
   echo form::hidden('blue', 0);
   echo form::hidden('hexcolor', '#74DF00');
   echo '<div style="background-color:#74DF00; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 188);
   echo form::hidden('green', 245);
   echo form::hidden('blue', 169);
   echo form::hidden('hexcolor', '#BCF5A9');
   echo '<div style="background-color:#BCF5A9; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

</tr>


<tr>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 11);
   echo form::hidden('green', 56);
   echo form::hidden('blue', 97);
   echo form::hidden('hexcolor', '#0B3861');
   echo '<div style="background-color:#0B3861; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 4);
   echo form::hidden('green', 95);
   echo form::hidden('blue', 180);
   echo form::hidden('hexcolor', '#045FB4');
   echo '<div style="background-color:#045FB4; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 88);
   echo form::hidden('green', 152);
   echo form::hidden('blue', 250);
   echo form::hidden('hexcolor', '#58ACFA');
   echo '<div style="background-color:#58ACFA; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

<td>
<?php 
   echo form::open('/item/tint/'.$item->id);
   echo form::hidden('red', 206);
   echo form::hidden('green', 236);
   echo form::hidden('blue', 245);
   echo form::hidden('hexcolor', '#CEECF5');
   echo '<div style="background-color:#CEECF5; float:left; width:80px; height:23px; margin-top:2px; border:1px solid #bbbbbb">&nbsp</div>';
   echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.select')) );
   echo form::close();
?>	
</td>

</tr>

</table>

<br style="clear:both;" />

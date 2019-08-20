<div class="pagetitle"><?php echo kohana::lang('admin.giveitems')?></div>
<?php echo $submenu ?>
<div id='helper'>E' possibile da questa pagina pulire la cache memcached totale del gioco.</div>
<?php echo form::open();?>
<center>
<?php echo form::hidden('dummy', 'x') ?>
<?php echo form::submit( array( 'id' => 'submit', 'class' => 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => 'Pulisci Cache'));?>
</center>
<?php echo form::close() ?>
<br style="clear:both;" />
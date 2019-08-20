<div class="pagetitle">Valuta richiesta di approvazione vestiti</div>
<?php echo $submenu ?> 
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div id= 'adminwardrobepreview'>

	<div style='float:left;width:300px;height:auto'>
	<fieldset>
	<legend>Set Standard</legend>
	<?php echo $request -> character -> render_char( $equippeditems, 'wardrobe' );?>
	</legend>
	</fieldset>
	</div>

	<div style='float:left;width:300px;height:auto;margin-left:5px'>
	<fieldset>
	<legend>Set Customizzato</legend>
	<?php echo $request -> character -> render_char( $equippeditems, 'preview' ); ?>
	</fieldset>
	</div>
	
	<br style="clear:both;"/>

</div>

<div id='licenze'>
	<fieldset>
	<legend>Licenze acquistate</legend>
	<table>	
	<?php
		foreach ( (array) $licenses as $license )
		{
		?>
			<tr>
			<td title='<?php echo html::image($license['preview'])?>'>ID: <?php echo $license['id'] ?>, tag: <?php echo $license['tag']?></td>
			</tr>			
		<?php			
		}
		?>
	</table>
	</fieldset>
</div>

<br/>


<?php echo form::open(); ?>
<?php echo form::hidden('id', $request -> id ) ?>
Causale rifiuto
<?php echo form::textarea( array( 
			'id' => 'reason', 'style' => 'width:100%;height:100px', 'name' => 'reason'), null	);?>
<center>			
<?php echo form::submit( array ('id' => 'submit', 'name' => 'AcceptCharge', 'class' => 'submit'), "Accetta e fai pagare"); ?>
&nbsp;
<?php echo form::submit( array ('id' => 'submit', 'name' => 'AcceptNoCharge', 'class' => 'submit'), "Accetta, non far pagare" ); ?>
&nbsp;
<?php echo form::submit( array ('id' => 'submit', 'name' => 'Refuse', 'class' => 'submit'), "Rifiuta"); ?>
</center>
			
<?php echo form::close(); ?>
<br style="clear:both;" />

<script>

$(document).ready(function()
{		
$(".character").autocomplete({
		source: "index.php/jqcallback/listallchars",
		minLength: 2
	});	
});
</script>

<div class="pagetitle">Multi Check</div>

<?php echo $submenu ?> 

<div class='helper'>Inserisci il nome del char e cerca tutti i char che hanno lo stesso valore di cookie o lo stesso IP address.</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<?php echo form::open(); ?>
<?php echo form::input( 
	array( 'id' => 'charactername', 
	'class' => 'character input-large', 
	'name' => 'charactername',
	'placeholder' => 'Mario Rossi'));
?>

<?php echo form::submit(
	array(
		'id' => 'search',
		'name' => 'searchip',
		'value' => 'Cerca chars (IP)', 
		'class' => 'button-normal'));?>
&nbsp;
<?php echo form::submit(
	array(
		'id' => 'search',
		'name' => 'searchcookie',
		'value' => 'Cerca chars (Cookie)', 
		'class' => 'button-normal'));?>
		
<?php echo form::close() ?>
<br/>
<hr/>
<br/>

<?php if (empty($characters) ) { ?>
<br/>
<p class='center'>Nessun record trovato o il cookie non è stato piazzato.</p>
<?php }
else
{
	$r = 0;
?>
	<table>
	<th>Nome</th>
	<th>IP</th>
	<th>Cookie</th>
	<th>Data Login</th>
	<th>Stato</th>
	<th>IP Shield</th>
	<th>Azioni</th>
	<?php foreach ($characters as $character) 
	{ 
		($r % 2 == 0) ? $class = '' : $class = 'alternaterow_1';		
		$hasipshieldbonus = Character_Model::get_premiumbonus( $character -> character_id, 'ipcheckshield' )
	
	?>
		<tr class="<?php echo $class;?>">
		<td class='center'><?php echo $character -> character_name; ?></td>
		<td class='center'><?php echo $character -> ipaddress; ?></td>
		<td class='center'><?php echo $character -> logincookie; ?></td>
		<td class='center'><?php echo $character -> logintime ?></td>
		<td class='center'><?php echo $character -> status ?></td>
		<td class='center'><?php echo ($hasipshieldbonus == false) ? 'No' : 'Yes' ?></td>
		<td class='center'>
		<?= html::anchor('/admin/changeuserstatus/' . $character -> user_id . '/active', 'Riattiva' ); ?>
		<br/>
		<?= html::anchor('/admin/changeuserstatus/' . $character -> user_id . '/suspended', 'Sospendi' ); ?>
		<br/>
		<?= html::anchor('/admin/changeuserstatus/' . $character -> user_id . '/canceled', 'Cancella' ); ?>
		</td>
		</tr>
	<?php 
		$r++;
	} ?>
	</table>
<?php 
} 
?>

<br style="clear:both;" />

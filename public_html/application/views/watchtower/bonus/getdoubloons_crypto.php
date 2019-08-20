<div class="pagetitle"><?php echo kohana::lang('bonus.buywithbitcoins')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>

<br/>

<div id='submenu'>
<?= html::anchor(
  '/bonus/getdoubloons',
  kohana::lang('global.back'),
  array(
    'class' => 'button' )
  );
?>
&nbsp;
</div>
<br/>
<h2 class='center'><?= kohana::lang('bonus.donatewithcrypto'); ?></h2>

<p><?= kohana::lang('bonus.donatewithcryptopar1'); ?></p>
<p><?= kohana::lang('bonus.donatewithcryptopar2'); ?></p>


<h2 class='center'><?= kohana::lang('bonus.wheretobuy'); ?></h2>

<table class='grid'>
<tr>
<th></th>
<th>Bitcoin Cash</th>
<th>Ethereum</th>
<th>Litecoins</th>
</tr>
<tr>
<td class='center'></td><td class='center'><?=kohana::lang('global.transactionfees');?>: <?= kohana::lang('global.low');?><br/><?=kohana::lang('global.delivery');?>: <?= kohana::lang('global.fast');?></td><td class='center'><?=kohana::lang('global.transactionfees');?>: <?= kohana::lang('global.low');?><br/><?=kohana::lang('global.delivery');?>: <?= kohana::lang('global.fast');?></td><td class='center'><?=kohana::lang('global.transactionfees');?>: <?= kohana::lang('global.low');?><br/><?=kohana::lang('global.delivery');?>: <?= kohana::lang('global.fast');?></td>
</tr>
<tr><td class='center'><?= kohana::lang('bonus.getwallet'); ?></td>
<td class='center'><?= html::anchor('https://www.coinbase.com/join/56a943a8ad1de46c8b0000d3', kohana::lang('bonus.coinbase'), array('target' => 'new')); ?></td>
<td class='center'><?= html::anchor('https://www.coinbase.com/join/56a943a8ad1de46c8b0000d3', kohana::lang('bonus.coinbase'), array('target' => 'new')); ?></td>
<td class='center'><?= html::anchor('https://www.coinbase.com/join/56a943a8ad1de46c8b0000d3', kohana::lang('bonus.coinbase'), array('target' => 'new')); ?></td>
</tr>


<tr><td class='center'><?= kohana::lang('global.buy'); ?></td><td class='center'><?= html::anchor('https://www.coinbase.com/join/56a943a8ad1de46c8b0000d3', kohana::lang('bonus.coinbase'), array('target' => 'new')); ?>
</td><td class='center'><?= html::anchor('https://www.coinbase.com/join/56a943a8ad1de46c8b0000d3', kohana::lang('bonus.coinbase'), array('target' => 'new')); ?></td><td class='center'><?= html::anchor('https://www.coinbase.com/join/56a943a8ad1de46c8b0000d3', kohana::lang('bonus.coinbase'), array('target' => 'new')); ?></td></tr>
</table>

<br/>

<table> 
<tr>
<td class='center'>
<b>-</b>
</td>
<td class='center'>
<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="8c16d8bf010baff7c5862707814e3662">
	<input type="hidden" name="item_name" value="1822 Doubloons">
	<input type="hidden" name="item_desc" value="1822 doubloons">
	<input type="hidden" name="item_number" value="1822">
	<input type="hidden" name="currency" value="USD">
	<input type="hidden" name="custom" value="<?=$char->user_id;?>">
	<input type="hidden" name="amountf" value="7.00000000">
	<input type="hidden" name="want_shipping" value="0">
	<input type="hidden" name="success_url" value="<?= url::base(TRUE, 'https')?>/page/display/purchaseddoubloons">	
	<input type="image" style='cursor:pointer;background:#eee;border:1px solid #555' src="https://www.coinpayments.net/images/pub/donate-small-grey.png" alt="Buy Now with CoinPayments.net">
</form>
<b>1822 Doubloons - 7 USD</b>
</td>
</tr>
<tr>
<td class='center'>
<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="8c16d8bf010baff7c5862707814e3662">
	<input type="hidden" name="item_name" value="4100 Doubloons">
	<input type="hidden" name="item_desc" value="4100 doubloons">
	<input type="hidden" name="item_number" value="4100">
	<input type="hidden" name="currency" value="USD">
	<input type="hidden" name="custom" value="<?=$char->user_id;?>">
	<input type="hidden" name="amountf" value="14.00000000">
	<input type="hidden" name="want_shipping" value="0">
	<input type="hidden" name="success_url" value="<?= url::base(TRUE, 'https')?>/page/display/purchaseddoubloons">	
	<input type="image" style='cursor:pointer;background:#eee;border:1px solid #555' src="https://www.coinpayments.net/images/pub/donate-small-grey.png" alt="Buy Now with CoinPayments.net">
</form>
<b>4100 Doubloons - 14 USD</b>
</td>
<td class='center'>
<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="8c16d8bf010baff7c5862707814e3662">
	<input type="hidden" name="item_name" value="10478 Doubloons">
	<input type="hidden" name="item_desc" value="10478 doubloons">
	<input type="hidden" name="item_number" value="10478">
	<input type="hidden" name="currency" value="USD">
	<input type="hidden" name="custom" value="<?=$char->user_id;?>">
	<input type="hidden" name="amountf" value="35.00000000">
	<input type="hidden" name="want_shipping" value="0">
	<input type="hidden" name="success_url" value="<?= url::base(TRUE, 'https')?>/page/display/purchaseddoubloons">	
	<input type="image" style='cursor:pointer;background:#eee;border:1px solid #555' src="https://www.coinpayments.net/images/pub/donate-small-grey.png" alt="Buy Now with CoinPayments.net">
</form>
<b>10478 Doubloons - 35 USD</b>
</td>
</tr>
<tr>
<td class='center'>
<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="8c16d8bf010baff7c5862707814e3662">
	<input type="hidden" name="item_name" value="21866 Doubloons">
	<input type="hidden" name="item_desc" value="21866 doubloons">
	<input type="hidden" name="item_number" value="21866">
	<input type="hidden" name="currency" value="USD">
	<input type="hidden" name="custom" value="<?=$char->user_id;?>">
	<input type="hidden" name="amountf" value="70.00000000">
	<input type="hidden" name="want_shipping" value="0">
	<input type="hidden" name="success_url" value="<?= url::base(TRUE, 'https')?>/page/display/purchaseddoubloons">	
	<input type="image" target='new' style='cursor:pointer;background:#eee;border:1px solid #555' src="https://www.coinpayments.net/images/pub/donate-small-grey.png" alt="Buy Now with CoinPayments.net">
</form>
<b>21866 Doubloons - 70 USD</b>
</td>
<td class='center'>
<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="8c16d8bf010baff7c5862707814e3662">
	<input type="hidden" name="item_name" value="45000 Doubloons">
	<input type="hidden" name="item_desc" value="45000 doubloons">
	<input type="hidden" name="item_number" value="45000">
	<input type="hidden" name="currency" value="USD">
	<input type="hidden" name="custom" value="<?=$char->user_id;?>">
	<input type="hidden" name="amountf" value="140.00000000">
	<input type="hidden" name="want_shipping" value="0">
	<input type="hidden" name="success_url" value="<?= url::base(TRUE, 'https')?>/page/display/purchaseddoubloons">	
	<input type="image" style='cursor:pointer;background:#eee;border:1px solid #555' src="https://www.coinpayments.net/images/pub/donate-small-grey.png" alt="Buy Now with CoinPayments.net">
</form>
<b>45000 Doubloons - 140 USD</b>
</td>
</tr>
</table>

<script type="text/javascript">

	$(document).ready(function()
	{
		$("#tabs").tabs({active: 0 });
		$("li.purchasebonus a").unbind('click');
	});

</script>

<div class="pagetitle"><?php echo kohana::lang('bonus.getdoubloons')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/><br/>
<div id="submenu">
	<?= html::anchor(
		'/bonus/index',
		kohana::lang('bonus.purchasebonuses'),
		array('class' => 'button')
	); ?>
</div>
<br/>
<p class='helper'><?= kohana::lang('bonus.buydoubloons_helper'); ?></p>


<fieldset>
<legend><?=kohana::lang('bonus.buywithbitcoins');?></legend>
<div class='center'>
<?= html::image('media/images/template/btc.png'); ?>
<?= html::image('media/images/template/ethereum.png'); ?>
<?= html::image('media/images/template/litecoin.png'); ?>
</div>
<h2 class="center"><?=kohana::lang('bonus.buywithbitcoinstitle');?></h2>
<br/>

<div class="center">
<table class="grid border" style="width:60%;margin:0 auto">
<th><?= kohana::lang('global.doubloons'); ?></th><th>Price</th>
<tr class="alternaterow_2"><td>1822</td><td><s>10 USD</s>&nbsp;7 USD</td></td></tr>
<tr class="alternaterow_1"><td>4100</td><td><s>20 USD</s>&nbsp;14 USD</td></td></tr>
<tr class="alternaterow_2"><td>10478</td><td><s>50 USD</s>&nbsp;35 USD</td></td></tr>
<tr class="alternaterow_1"><td>21866</td><td><s>100 USD</s>&nbsp;70 USD</td></td></tr>
<tr class="alternaterow_1"><td>45000</td><td><s>200 USD</s>&nbsp;140 USD</td></td></tr>
</table>
</div>
<br/>
<div class='center'>
<?= html::anchor('bonus/getdoubloons_crypto',
	kohana::lang('global.buy'), array('class' => 'button button-medium')); ?>
</div>
</fieldset>
<br/>

<?php /*

<fieldset>
<legend><?=kohana::lang('bonus.buywithpaypal');?></legend>

<div class='center'>
<?= html::image('media/images/template/paypal.png' ); ?>
<br/>
<?php //echo html::anchor('https://www.dropbox.com/s/13bfmd5v3wsd7v8/Tutorial%20Paypal.docx?dl=0', kohana::lang('bonus.buyfromadrtutorial'), array('target' => 'new') ); ?>
</div>

<p class='center'><?= kohana::lang('bonus.buybitcoinsfromresellers'); ?></p>

<p class='center'>
<?= kohana::lang('bonus.writetoreseller',
html::anchor('message/write/0/new/15244', 'Tebaldo Colleoni' ),
html::anchor('#', '-' ),
html::anchor('message/write/0/new/69676', 'Cathal of Ayre' )
); ?>
</p>

<div class="center">
<table class="grid" style="width:60%;margin:0 auto">
<th><?= kohana::lang('global.doubloons'); ?></th><th>Price</th>
<tr class="alternaterow_1"><td>820</td><td>5 USD</td></tr>
<tr class="alternaterow_2"><td>1822</td><td>10 USD</td></tr>
<tr class="alternaterow_1"><td>4100</td><td>20 USD</td></tr>
<tr class="alternaterow_2"><td>10478</td><td>50 USD</td></tr>
<tr class="alternaterow_1"><td>21866</td><td>100 USD</td></tr>
<tr class="alternaterow_2"><td>45000</td><td>200 USD</td></tr>
</table>
</div>



<br/>
</fieldset>
**/ ?>

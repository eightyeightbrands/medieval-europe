<script>

 $(document).ready(function()
 {	
	
	function computesenddata()
	{
		
		
		$.ajax( //ajax request starting
		{
		url: '<?php echo url::base(true)?>' + 'item/computesenddata', 
		type:"POST",
		data: { 			
			quantity: $("#quantity").val(),
			item_id: $("[name=item_id]").val(),
			target: $("#to").val(),			
		},
		success: 
		function(data) 
			{									
				info = JSON.parse( data );				
				if ( info.rc == 'NOK' )
				{
					$('#error').text("Error: " + info.message);	
					$('#cost').text('-');
					$('#time').text('-');
					$('#error').show();						
				}
				else
				{
					$('#error').hide();					
					$('#cost').text(info.cost); 
					$('#time').text(info.timetext); 		
					$('#priceandtime').show();						
				}					
			}
		});		
	}
	
	computesenddata();	
	
	$("#to").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2,		
	});		
	
	$("#to, #quantity").change(function()	
	{								
		computesenddata();		
	})
	
 });
</script>
<div class= 'pagetitle'><?php echo kohana::lang('charactions.senditem'); ?></div>


<?=
	html::anchor( 
		'character/inventory/',
		'Back',
		array('class' => 'button button-small')
	); 
?>


<br/>
<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('charactions.senditem_helper') ; ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Sending_Items',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>
<fieldset class='center'>
<br/>
<fieldset>
<?php 
	
	echo form::open('/item/send');
	echo form::hidden('item_id', $item -> id);
	echo kohana::lang('charactions.senditem_totalitemsmessage', $item->quantity, kohana::lang($item->cfgitem->name)) ;
	echo '<br/><br/>';
	echo kohana::lang('charactions.senditem_sendnormalitem') . ' item id ' . $item -> id . ': ' ;
	
	if ( ! in_array ( $item -> cfgitem -> tag, array( 'doubloon', 'silvercoin', 'coppercoin' ) ) )
		echo form::input( array( 
		'id' => 'quantity', 
		'name' => 'quantity', 
		'value' => $item -> quantity,  		
		'class' => 'input-xxsmall',
		'style'=>'text-align:right') );
	else
		echo form::input( array( 
			'id'=>'quantity', 
			'name' => 'quantity', 
			'value' =>  $form['quantity'], 
			'class' => 'input-xsmall',
			'style'=>'text-align:right') );
	
	if (!empty ($errors['quantity'])) 
		echo "<div class='error_msg'>".$errors['quantity']."</div>";
	
	echo '&nbsp;' . kohana::lang($item->cfgitem->name) . '&nbsp;' . kohana::lang('global.to') . '&nbsp;' ;
	
	echo form::input( array( 
		'id' => 'to', 
		'name' => 'to', 
		'value' =>  $form['to'], 
		'class' => 'input-medium',
	));
		
	echo '<br/><br/>';	
	
	echo "<div class='evidence' id='error'></div>";
	
	echo "<div class='center' id='priceandtime' >";
	echo kohana::lang('charactions.sendinfo');
	echo "<div>";
	
	echo "<br style='clear:both'/>";
	
	echo 
		"<div style='text-align:center'>" .
		form::submit( array( 'id' => 'senditem', 'class' => 'button button-medium',  'value' => Kohana::lang('global.send')) )."</div>";
	
	echo form::close();
?>
</fieldset>
<br style='clear:both'/>	

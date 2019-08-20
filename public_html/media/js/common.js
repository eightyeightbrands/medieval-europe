$(document).ready(function() 
{ 
	
	/////////////////
	//  Side Chat
	/////////////////

	$( function ()
	{
	
		// if cookies says that sidechat is visible then load side chat.

		var chatvisible = Cookies.get('chatvisible');
		if (typeof chatvisible == 'undefined')
			chatvisible = 'none';

		if (chatvisible == 'sidechat')
		{
			$('#sidechatiframe').attr('src', 'index.php/newchat/init?type=side');
			$('#sidechat').show();
			$("#showchat").hide();
		}

		// if cookie says that fullchat is visible, hide side chat.
		if (chatvisible == 'fullchat' )
		{
			$('#sidechatiframe').attr('src', '');	
			$('#sidechat').hide();
			$("#showchat").show();
		}

		// if player clicks fullchat, hide side chat
		$('.fullchat').click( function()
		{	
			
			$('#sidechatiframe').attr('src', '');
			$('#sidechat').hide();		
			Cookies.set('chatvisible', 'full');	
			$("#showchat").show();
			window.location='index.php/newchat/init';
			
		});
		// hide side chat
		$('#hidechat').click( function()
		{
			$('#sidechatiframe').attr('src', '');
			$('#sidechat').hide();		
			Cookies.set('chatvisible', 'none');	
			$("#showchat").show();
		});
		// show hide chat, only if chatvisible is none
		$('#showchat').click( function()
		{
			if (chatvisible != 'none')
				return false;
			$('#sidechatiframe').attr('src', 'index.php/newchat/init?type=side');
			$('#sidechat').show();	
			Cookies.set('chatvisible', 'sidechat');
			$("#showchat").hide();
		});
	}),	
	$('[title]').not(".itemnamerow, .itemname").tooltipster({
		theme: 'tooltipster-borderless',
		contentAsHTML: true,
		interactive: true,
		maxWidth: 300,
		}
	);
	
});

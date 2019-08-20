<head>
<script>
$(document).ready
(
function()
{
	$('.screenshot').click( 
		function(event) { 
			id = event.target.id;				
			src = '<?php echo url::base() ?>' + 'media/images/template/screenshots/' + id + '.png';			
			$('#pic'+id).attr('src', src );
			$('#pic'+id).dialog('close');
			$('#pic'+id).dialog(
			{ 				
				modal: true,				
				show: "drop",
				dialogClass: "alert",	
				position: [300, 100],
			});
		});
});
</script>

<style>
#screenshots td:hover
{ cursor:pointer; }

img.screenshot_large, .alert
{ padding:2px; background-color: #fff; }

p { padding:0 }

#blurb
{ float:left; width:48%; margin-right:4%; }

#login
{ float:left; width:22%; margin-right:4%; }

#blurb h1, #login h1, #screens h1
{ margin-bottom:5px; color:#8b4513; }

#social
{ float: right; text-align:right; }

#form
{ margin-left:0px; }

.regb
{ font-weight:bold; float:left; margin-left:0px; }

#screens
{ float:left; width:22%; }

.screenshot
{ float:left; }

.screenshot img
{ padding:2px; border:1px solid #777; }
</style>
</head>

<body>
<div>
	<div id='main-text'>
	<div id='blurb'>
		<h1><?php echo kohana::lang('page-homepage.homepageheader')?></h1>
		<p style='text-align:left'>
			<?php echo kohana::lang('page-homepage.burbletext') ?>			
		</p>
		<button class='regb'><?php echo kohana::lang('page-homepage.signup')?></button>
		<div id='social'>
		<?php
			echo html::anchor('https://www.facebook.com/pages/Medieval-Europe/108773142496282', 
			html::image(array('src' => 'media/images/template/fb.png'),
			array('title' => Kohana::lang('page-homepage.fb_followus'), 'class' => 'littleicon')), array( 'target' => 'new') ); 
			echo html::anchor('https://twitter.com/Medieval_Europe', 
			html::image(array('src' => 'media/images/template/twitter.png'),
			array('title' => Kohana::lang('page-homepage.tw_followus'), 'class' => 'littleicon')), array( 'target' => 'new') );
		?>
		</div>
	</div>
	<div id='login'>
		<h1><?php echo Kohana::lang('page-homepage.login-signup'); ?></h1>
		<div style="margin-bottom:10px; text-align:left"><?php echo Kohana::lang('page-homepage.login-signup-info1'); ?></div>
		<div id="form">
			<?php echo form::open('/user/login') ?>
			<?php echo form::input( array( 'name'=>'username', 'value' => null, 'id'=>'username' , 'style' => 'width:175px') ); ?><br/>
			<?php echo form::password( array( 'name'=>'password', 'value' => null, 'id'=>'password' , 'style' => 'width:175px') ); ?><br/>
			<?php echo form::submit( array ('id' => 'submit', 'class' => 'button button-medium' ), kohana::lang('user.login_submit')); ?>
			<?php echo form::close() ?>
			<br style='clear:both'/>
			<br/><br/>
			<?php echo html::anchor('/user/resendpassword', kohana::lang('user.login_resendpassword')); ?><br/>
			<?php echo html::anchor('/user/resendvalidationtoken', kohana::lang('user.resendvalidationtoken_pagetitle')); ?>
		</div>
	</div>
	
	<div id="screens">
		<h1><?php echo kohana::lang('page-homepage.screenshots')?></h1>	
		<div style='margin-top:10px;'>
				
				<div class='screenshot' style="margin-right:6px;">
					<?php echo html::image('media/images/template/screenshots/homescr1_t.png', array('id' => 'scr1', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr1', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot'>
					<?php echo html::image('media/images/template/screenshots/homescr2_t.png', array('id' => 'scr2', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr2', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot' style="margin-right:6px;">
					<?php echo html::image('media/images/template/screenshots/homescr3_t.png', array('id' => 'scr3', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr3', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot'>
					<?php echo html::image('media/images/template/screenshots/homescr4_t.png', array('id' => 'scr4', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr4', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot' style="margin-right:6px;">
					<?php echo html::image('media/images/template/screenshots/homescr5_t.png', array('id' => 'scr5', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr5', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot'>
					<?php echo html::image('media/images/template/screenshots/homescr6_t.png', array('id' => 'scr6', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr6', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot' style="margin-right:6px;">
					<?php echo html::image('media/images/template/screenshots/homescr7_t.png', array('id' => 'scr7', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr7', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
				<div class='screenshot'>
					<?php echo html::image('media/images/template/screenshots/homescr8_t.png', array('id' => 'scr8', 'alt' => 'Medieval Europe') )?></td>			
					<?php echo html::image( '', array('id' => 'picscr8', 'class'=>'screenshot_large', 'style' => 'display:none') )?>	
				</div>
			</div>
	</div>
	<br style='clear:both'/>
	</div>
</div>
</body>

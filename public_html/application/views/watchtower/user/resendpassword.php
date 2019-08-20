<div class="row">
	<div class="col-xs-12">
		<div class="text-center">
			<?php $message = Session::instance() -> get('user_message'); echo $message ?></div>
			<h1 class="text-center"><?php echo kohana::lang('user.resendpassword_pagetitle') ?></h1>
		
			<ul class="list-inline text-center">
			<li>
				<?php echo html::anchor('/', Kohana::lang('page-homepage.goto-homepage')); ?>
			</li>
			</ul>
			
			<p class="text-center"></p>
		</div>
</div>
<div class="row">
	<div class="col-xs-6 text-right">
		<?= form::open('/user/resendpassword', array('class' => 'form-inline')); ?>
		<div class="form-group">
			<label for="email"><?=Kohana::lang('user.resendpassword_email');?></label>
			<?= form::input(
						array( 
							'id' => 'email',
							'name' => 'email',
							'placeholder' => 'email@example.com',
							'class' => 'form-control'
						)
					);
				?>
		</div>
		<?
			if (!empty ($errors['email'])) { 
		?>
			<div class='alert alert-danger text-right'>"<?php echo $errors['email']?></div>
		<?
			}
		?>			
	</div>
	<div class="col-xs-2 text-left">
		<div class="form-group">
		<?=	form::submit(
					array (
						'id' => 'submitresendpassword' , 
						'name' => 'submitredenspassword',
						'class' => 'btn btn-me'),
						kohana::lang('user.resendpassword_submit')
				);	
		?>
		<?php echo form::close(); ?>
		</div>
	</div>									
</div>


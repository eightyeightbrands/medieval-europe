<link rel="canonical" href="<?php echo url::site('/');?>"/>

<?php
	if (!empty($pixelgifsrc))
		echo html::image($pixelgifsrc);

?>

<div class="row">

	<div class="col-xs-12">

		<div class="alert alert-danger text-center">
			<?php $message = Session::instance() -> get('user_message'); echo $message ?>
		</div>

		<h1 class="text-center"><?php echo kohana::lang('page-homepage.gameheader')?></h1>

		<span class="lead">
		<?php echo kohana::lang('page-homepage.burbletext'); ?>
		</span>

	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-md-4 col-md-offset-1">
			<h4 class="text-center"><?= kohana::lang('page-homepage.signin');?></h4>
			<?php echo form::open('/user/login') ?>

			<div class="form-group">
				<?php echo form::input( array(
					'name'=>'username',
					'value' => null,
					'tabindex' => 1,
					'id' => 'username1',
					'placeholder' => kohana::lang('page-homepage.yourusername'),
					'class' => 'form-control') ); ?>
			</div>

			<div class="form-group">

				<?php echo form::password( array(
					'name'=>'password',
					'value' => null,
					'tabindex' => 2,
					'id' => 'password',
					'placeholder' => kohana::lang('page-homepage.yourpassword'),
					'class' => 'form-control') ); ?>
			</div>

			<div class="btn-group btn-group-justified">
				<div class="col-xs-12" style="padding:0 1px">
					<?php echo form::submit(
						array (
							'id' => 'signin',
							'class' => 'btn btn-me',
							'tabindex' => 3,
						), kohana::lang('page-homepage.signin'));
					?>
				</div>
				<div class="col-xs-6" style="padding:0 1px;color:yellow">
					<a href='/facebook' tabindex = 5, class = 'btn btn-fb'>Facebook Login</a>
				</div>
				<div class="col-xs-6" style="padding:0 1px">
					<?php
						echo html::anchor(
							$google_login_url,
							'Google Login',
							array(
								'tabindex' => 5,
								'class' => 'btn btn-google'
							)
					);
					?>
				</div>
			</div>

			<?php echo form::close() ?>

			<div class="form-group text-right">
					<div class="col-xs-12 col-md-4 text-left" style="padding:0 1px">
						<ul class="list-inline">
							<li>
							<?php
								echo html::anchor('https://www.facebook.com/pages/Medieval-Europe/108773142496282',
									html::image(
										array(
											'src' => 'media/images/template/fb.png',
											'alt' => 'Medieval Europe Facebook' )),
									array(
										'title' => Kohana::lang('page-homepage.fb_followus'),
										'class' => 'littleicon',
										'target' => 'new' ) );
							?>
							</li>
							<li>
							<?
								echo html::anchor('https://google.com/+MedievaleuropeEuGame',
									html::image(
										array(
											'src' => 'media/images/template/gp.png',
											'alt' => 'Medieval Europe Google+' )),
									array(
										'title' => Kohana::lang('page-homepage.google_followus'),
										'class' => 'littleicon',
										'target' => 'new'));
							?>
							</li>
							<li>
							<?
								echo html::anchor('https://twitter.com/Medieval_Europe',
									html::image(
										array(
											'src' => 'media/images/template/twitter.png',
											'alt' => 'Medieval Europe Twitter' )),
									array(
										'title' => Kohana::lang('page-homepage.tw_followus'),
										'class' => 'littleicon',
										'target' => 'new' ) );
							?>
							</li>
						</ul>
					</div>
					<div class="col-xs-12 col-md-8 text-right" style="padding:0 1px">
					<?= html::anchor('/user/resendpassword', kohana::lang('user.login_resendpassword')); ?>
					<br/>
					<?= html::anchor('/user/resendvalidationtoken', kohana::lang('user.resendvalidationtoken_pagetitle'));?>
					</div>
			</div>
			<div class="form-group text-center">

			</div>
	</div>

	<div class="col-xs-12 col-md-4 col-md-offset-1">
	<!-- signup -->

		<h4 class="text-center"><?= kohana::lang('page-homepage.signup');?></h4>
		<?php echo form::open('/user/register') ?>
			<div class="form-group row">
			<div class="col-xs-12 text-center">
				<?= form::input( array(
					'name'=>'username',
					'value' => $form['username'],
					'tabindex' => 5,
					'placeholder' => kohana::lang('page-homepage.yourusername'),
					'id' => 'username2',
					'class' => 'form-control') );
				?>

				<? if (!empty ($errors['username']))
				{
				?>
					<div class="alert alert-danger text-left"><?= $errors['username']; ?></div>
				<? } ?>
			</div>
			</div>

			<div class="form-group row">
			<div class="col-xs-12 text-center">
				<?php echo form::input( array(
					'name' => 'email',
					'value' => $form['email'],
					'tabindex' => 6,
					'placeholder' => kohana::lang('page-homepage.youremail'),
					'id' => 'email',
					'class' => 'form-control') );
				?>

				<? if (!empty ($errors['email']))
				{
				?>
					<div class="alert alert-danger text-left"><?= $errors['email']; ?></div>
				<? } ?>
				</div>
			</div>


			<div class="form-group row">
				<div class="col-xs-12 text-center">
				<?php
						echo form::input( array(
								'name' => 'referreruser',
								'tabindex' => 7,
								'placeholder' => kohana::lang('page-homepage.referraluser'),
								'value' => $form['referreruser'],
								'id' => 'referreruser',
								'class' => 'form-control') );
				?>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-xs-12 text-center">
					<div class="g-recaptcha" data-theme = 'dark' data-sitekey="6Lf_v3MUAAAAAFs0o7zvMGoDd1XUvFSAP3qDq49Q"></div>

				 <? if (!empty ($errors['captchaanswer']))
					{
					?>
        <div class="alert alert-danger text-left"><?= $errors['captchaanswer']; ?></div>
				<? } ?>

			</div>

			<div class="form-group row">
				<div class="col-xs-12">
				<?php echo form::submit(
						array (
						'id' => 'signup',
						'class' => 'btn btn-me',
						'onclick' => 'fbq(\'track\', \'CompleteRegistration\');',
						'tabindex' => 7,
						'style' => 'width:100%'
						), kohana::lang('page-homepage.signup')); ?>
				</div>
			</div>

			<div>
			<small>
				<?=
					kohana::lang('page-homepage.tosacceptance',
						html::anchor(
							'/page/display/terms-of-use',
							kohana::lang('page-homepage.tos')
						));
				?>
			</small>

			</div>

	</div>
</div>	<!-- container -->

<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/app.css' ?>">
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/form_settings.css' ?>">
<div class="amoforms amoforms-account">
	<div class="amoforms__message amoforms__success_message">
		<p>Changes successfully saved</p>
	</div>
	<div class="amoforms__message amoforms__error_message">
		<p>Changes not saved</p>
	</div>
	<div class="wrap amoforms_form-setting_page amoforms_account-setting_page">
		<a href="http://www.amocrm.com" target="_blank" class="amoforms_account-setting_page-logo"></a>
		<?php
		if ($this->get('has_error')) {
			?>
			<div class="amoforms_account-setting_page-notofication">
				<h3><span>!</span>amoCRM authorization error</h3>
				<p>Request data cannot be uploaded to your amoCRM account. <br>You might have changed your password or subdomain.</p>
				<p>Form is temporarily unavailable.<br>Please, enter new login details to your account</p>
			</div>
			<?php
		}
		;?>
		<h2>Account settings</h2>
		<p class="amoforms_form-setting_desc">
			You can change your authorization data here
		</p>
		<form method="post" action="" novalidate="novalidate" id="account-settings">
			<?php settings_fields($this->get('nonce_field')); ?>
			<div class="amoforms__section_top">
				<div class="amoforms__form-setting__row__inner">
					<span class="amoforms__form-setting__row__inner__name" >User login</span>
					<input type="text"
						   name="login" value="<?php echo $this->get('login') ?>"
						   title="API key" placeholder="My awesome name"
						   class="amoforms__form-setting__text-input"
						   id="account_settings_user_login">
				</div>
				<div class="amoforms__form-setting__row__inner">
					<span class="amoforms__form-setting__row__inner__name" >API key</span>
					<input type="text"
						   name="api_key" value="<?php echo $this->get('api_key') ?>"
						   title="API key" placeholder="<?php echo md5(rand(0, 1000)) ?>"
						   class="amoforms__form-setting__text-input"
						   id="form_settings_api_key">
				</div>
				<div class="amoforms__form-setting__row__inner">
					<span class="amoforms__form-setting__row__inner__name" >Account subdomain</span>
					<input type="text"
						   name="subdomain" value="<?php echo $this->get('subdomain') ?>"
						   title="API key" placeholder="MyAwesomeDomain"
						   class="amoforms__form-setting__text-input"
						   id="form_settings_subdomain">
				</div>
			</div>
			<div class="amoforms__section_bottom">
				<input type="hidden" name="action" value="update_account">
				<div class="amoforms_save_form_block">
					<div class="second_form_buttons">
						<input type="submit" value="save" class="save_button" id="submit_button">
						<span class="cancel_button">cancel</span>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
wp_enqueue_script(
	'amoforms_account_settings',
	plugins_url('/amoforms/js/settings/account.js'),
	array(
		'jquery',
	)
);
?>

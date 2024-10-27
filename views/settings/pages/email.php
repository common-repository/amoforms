<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Controllers\Settings;
use Amoforms\Libs\GeoIP\IpInfo;

/** @var \Amoforms\Models\Forms\Form $form */
$form = $this->get('form');
$settings = $form->get_settings();
$registered = $this->get('registered');
$is_blocked = $this->get('is_blocked'); //TODO: use or remove

$page_settings = [
	'account'              => [
		'login'      => $this->get('login'),
		'subdomain'  => $this->get('subdomain'),
		'api_key'    => $this->get('api_key'),
		'registered' => (bool)$registered,
		'url'        => $this->get('account_url'),
		'short_url'  => $this->get('account_short_url'),
	],
	'account_actions'      => $this->get('account_actions'),
	'is_blocked'           => (bool)$is_blocked,
	'show_stats_reporting' => (bool)$this->get('show_stats_reporting'),
	'libphonenumber_path'  => plugins_url('/amoforms/js/vendor/libphonenumber/build/utils.js'),
	'country'              => 'us',
];

if (!$registered && $ip_info = IpInfo::instance()->get_info($_SERVER['REMOTE_ADDR'])) {
	if (!empty($ip_info['country'])) {
		$page_settings['country'] = strtolower($ip_info['country']);
	}
}
?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/app.css' ?>">
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/form_settings.css' ?>">
<link rel="stylesheet" href="<?php echo plugins_url('/amoforms/js/vendor/intl-tel-input/build/css/intlTelInput.css') ?>">

<script>
	window.AMOFORMS = window.AMOFORMS || {};
	AMOFORMS.page_settings = <?php echo json_encode($page_settings) ?>;
</script>

<div class="amoforms amoforms-email">
	<div class="amoforms__message amoforms__success_message" id="amoforms__success_message_wrapper">
		<p id="amoforms__success_message_text">Changes successfully saved</p>
	</div>
	<div class="amoforms__message amoforms__error_message" id="amoforms__error_message_wrapper">
		<p id="amoforms__error_message_text">Changes not saved</p>
	</div>

	<form id="email-settings">
		<?php //settings_fields($this->get('nonce_field')); ?>
		<input type="hidden" name="form[id]" value="<?php echo $form->id() ?>">
		<input type="hidden" name="account_action" id="amoforms_account_action"
			   value="<?php echo $registered ? Settings::ACCOUNT_ACTION_UPDATE : Settings::ACCOUNT_ACTION_REGISTER ?>"
			>
	<div class="wrap amoforms_form-setting_page amoforms_email-setting_page">
		<h2>Notification Settings</h2>
		<p class="amoforms_form-setting_desc">
			Receive an email of the submitted data when someone completes the form
		</p>
		<div class="amoforms__section_top">
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Your Name</span>
				<input type="text"
					   id="amoforms_email_name"
					   class="amoforms__form-setting__text-input amoforms__js-form-input"
					   placeholder="John"
					   name="form[settings][email][name]"
					   value="<?php echo $settings['email']['name'] ?>"
					   tabindex="10"
					><br />
				<span class="amoforms__form-setting__row__inner__descr">This option sets the "From" display name of the email that is being sent</span>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Email Subject</span>
				<input type="text"
					   id="amoforms_email_subject"
					   class="amoforms__form-setting__text-input amoforms__js-form-input"
					   placeholder="Call Back Request"
					   name="form[settings][email][subject]"
					   value="<?php echo $settings['email']['subject'] ?>"
					   tabindex="20"
					><br />
				<span class="amoforms__form-setting__row__inner__descr">This sets the subject of the email that is being sent</span>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Email To</span>
				<input type="email"
					   id="form_settings_email_to"
					   class="amoforms__form-setting__text-input amoforms__js-form-input"
					   placeholder="John@gmail.com"
					   name="form[settings][email][to]"
					   value="<?php echo $settings['email']['to'] ?>"
					   tabindex="30"
					><br />
				<span class="amoforms__form-setting__row__inner__descr">Who submitted data being sent to</span>
			</div>
		</div>

				<div class="amoforms__form-setting__row__inner amoforms__stats_reporting <?php echo $this->get('show_stats_reporting') ? '' : 'hidden' ?>" id="amoforms__stats_reporting_wrapper">
					<span class="amoforms__form-setting__row__inner__name"></span>
					<div style="display: inline-block;">
						<input type="checkbox" id="stats_reporting_checkbox" name="stats_reporting" value="1" style="display: inline-block; vertical-align: top; margin-top: 1px;" <?php echo $registered ? 'checked' : '' ?> required title="" tabindex="40">
						<span style="display: inline-block; vertical-align: top; font-size: 13px;">
							amoForms plugin works with amoCRM.
							Using this plugin you accept the
							<br><a href="" id="show_terms">Terms of User Agreement</a>
						</span>
						<div class="amoforms-offer" id="amoforms_offer">
							<?php require_once __DIR__ . '/../parts/terms_of_use.php'; ?>
						</div>
					</div>
				</div>

		<div id="amo_account_settings"
			 class="amo_account_settings_wrapper <?php echo $registered ? '' : 'hidden' ?>"
			 data-state="<?php echo $registered ? 'connected' : 'edit' ?>"
			>

			<div id="amo_forms_credentials-settings" class="amo_forms_credentials">
				<div class="amo_forms_credentials__intro">
					<div class="amo_forms_credentials__intro_title">
						<img src="<?php echo plugins_url('/amoforms/images/amo-logo.png')?>" height="37" width="162" alt="">
					</div>
					<div class="amo_forms_credentials__intro_wrapper amo_forms_credentials__intro_wrapper-settings"
						 id="amo_account_settings_title_edit">
						<div class="amo_forms_credentials__intro_subtitle">Already an amoCRM User?</div>
						<div class="amo_forms_credentials__intro_description">
							Set up integration with web form and you will be able to process all
							the incoming leads and control the whole cycle of the lead in one place.
						</div>
					</div>
					<div class="amo_forms_credentials__intro_wrapper amo_forms_credentials__intro_wrapper-error"
						 id="amo_account_settings_title_connection_error">
						<div class="amo_forms_credentials__intro_subtitle">Error While Connecting to amoCRM</div>
						<div class="amo_forms_credentials__intro_description">
							We were unable to integrate your form with your amoCRM account,
							please check the details you have entered and try again.
						</div>
					</div>
					<div class="amo_forms_credentials__intro_wrapper amo_forms_credentials__intro_wrapper-done"
						 id="amo_account_settings_title_connected">
						<div class="amo_forms_credentials__intro_subtitle">Your Form is Integrated with amoCRM</div>
						<div class="amo_forms_credentials__intro_description">
							Now all client's details filled in on the website will automatically show in amoCRM
							where you will be able to control the whole process of lead conversion.
						</div>
					</div>
					<div class="amo_forms_credentials__intro_wrapper amo_forms_credentials__intro_wrapper-just_registered"
						 id="amo_account_settings_title_just_registered">
						<div class="amo_forms_credentials__intro_subtitle">amoCRM Takes Care of Your Clients</div>
						<div class="amo_forms_credentials__intro_description">
							<p>
								The system won’t let you lose any leads and will give you full control of the lead conversion process.
							</p>
							<p style="height: 90px">
								<a href="//www.amocrm.com/support/videos.php" target="_blank" class="amo_forms_credentials__video">
									<img src="<?php echo plugins_url('/amoforms/images/video_img.png')?>" height="63" width="110" alt="Video">
								</a>
								<span class="amo_forms_credentials__video_description">Watch this short video to find out about amoCRM.</span>
							</p>
						</div>
					</div>
				</div>

				<div class="amo_forms_credentials__intro_description_dots"></div>

				<div class="amo_forms_credentials__intro amo_forms_credentials__intro_bottom">
					<p style="margin-top: 10px">
						We have created an account for you, where all the details from filled in forms will be sent.
						Try to explore all the advantages of amoCRM during your 14 day free trial.
					</p>
					<p style="margin-top: 15px;">
						<span class="amo_forms_credentials__title">Your account address:</span>
						<span class="amoforms_subdomain_link js_amoforms_go-to-amocrm-account_link"><span class="js-amoforms_account_url"><?php echo $this->get('subdomain') ?: 'www' ?>.amocrm.com</span></span>
					</p>
					<p style="margin-top: 10px; margin-bottom: 10px;">
						<span class="amo_forms_credentials__title">Your API key:</span>
						<span class="amoforms_api_key_value"><?php echo $this->get('api_key') ?></span>
					</p>
				</div>

				<div class="amoforms__form-setting__wrapper amo_account_inputs_wrapper" id="amo_account_inputs_wrapper">
					<div class="amoforms__form-setting__row__inner">
						<span class="amoforms__form-setting__row__inner__name">Login</span>
						<input type="text"
							   id="account_settings_user_login"
							   class="amoforms__form-setting__text-input amoforms__js-form-input"
							   name="amo_user[login]"
							   value="<?php echo $this->get('login') ?>"
							   placeholder="demo@example.com"
							   tabindex="50"
							><br>
						<span class="amoforms__form-setting__row__inner__descr">Login of your amoCRM account.</span>
					</div>
					<div class="amoforms__form-setting__row__inner">
						<span class="amoforms__form-setting__row__inner__name">Subdomain</span>
						<input type="text"
							   id="form_settings_subdomain"
							   class="amoforms__form-setting__text-input amoforms__js-form-input"
							   name="amo_user[subdomain]"
							   value="<?php echo $this->get('subdomain') ?>"
							   title="Subdomain"
							   placeholder="MyAwesomeDomain"
							   tabindex="60"
							> .amocrm.com<br>
						<span class="amoforms__form-setting__row__inner__descr">You can find your subdomain in the field Address in General settings of your amoCRM account.</span>
					</div>
					<div class="amoforms__form-setting__row__inner">
						<span class="amoforms__form-setting__row__inner__name">API key</span>
						<input type="text"
							   id="form_settings_api_key"
							   class="amoforms__form-setting__text-input amoforms__js-form-input"
							   name="amo_user[api_key]"
							   value="<?php echo $this->get('api_key') ?>"
							   title="API key"
							   placeholder="<?php echo md5(rand(0, 1000)) ?>"
							   maxlength="40"
							   tabindex="70"
							><br>
						<span class="amoforms__form-setting__row__inner__descr">You can find your API key in Profile settings of your amoCRM account.</span>
					</div>
				</div>
		        <div class="amoforms_credentials__links">
					<a class="amoforms_link amoforms_link-primary amoforms_credentials__links__goto js_amoforms_go-to-amocrm-account_link" href="#">Go to amoCRM</a>
					<a class="amoforms_link amoforms_link-default amoforms_credentials__links__change" id="amoforms_change-account-settings-btn" href="#">Change subdomain and API key</a>
				</div>
			</div>
		</div>

		<?php /*if (FALSE): temporary disabled ?>
		<div class="amoforms__section_middle">
			<div class="amoforms__checkbox_wrapper">
				<input type="checkbox">If File uploads are present, include the uploaded files with the email.
				<p class="amoforms__section_middle__descr">
					NOTE: many severs restrict email attachment size to 25MB or even smaller. <br>
					if you experience trouble sendingor receiving emails with this setting on, reduce the File Upload max file size.
				</p>
			</div>
		</div>
		<?php endif */?>

		<!--
		<h2>Autoresponder</h2>
		<p class="amoforms_form-setting_desc">
			Send an email to the person who completed the form.
		</p>-->
		<div class="amoforms__section_bottom">
			<?php /*
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Your Name</span>
				<input type="text" class="amoforms__form-setting__text-input" placeholder="John"><br />
				<span class="amoforms__form-setting__row__inner__descr">This option sets the "From" display name of the email that is sent.</span>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Reply To Email</span>
				<input type="email" class="amoforms__form-setting__text-input" placeholder="John@gmail.com"><br />
				<span class="amoforms__form-setting__row__inner__descr">Replies to the submission email will go here.</span>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Email Subject</span>
				<input type="text" class="amoforms__form-setting__text-input" placeholder="Call Back Request"><br />
				<span class="amoforms__form-setting__row__inner__descr">This sets the subject of the email that is sent.</span>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Email To</span>
				<input type="email" class="amoforms__form-setting__text-input" placeholder="John@gmail.com"><br />
				<span class="amoforms__form-setting__row__inner__descr">Who to send the submitted data to.</span>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Message</span>
				<textarea class="amoforms__form-setting__textarea"></textarea>
			</div>
			<div class="amoforms__form-setting__row__inner">
				<span class="amoforms__form-setting__row__inner__name">Append Entry</span>
				<input type="email" class="amoforms__form-setting__text-input" placeholder=""><br />
				<span class="amoforms__form-setting__row__inner__descr">include a copy of the Users's Entry</span>
			</div>
			</div>
			*/ ?>

			<div class="amoforms_submit_buttons">
				<input type="hidden" name="action" value="amoforms_update_email">
				<input class="save_button" type="submit" value="Save" data-disabled="1" id="submit_button" tabindex="1000">
				<?php //<span class="cancel_button">cancel</span> ?>
			</div>
		</div>
	</div>
	</form>
</div>
<?php
wp_register_script('intl-tel-input', plugins_url('/amoforms/js/vendor/intl-tel-input/build/js/intlTelInput.min.js'), array('jquery'));
wp_register_script('amoforms_fn', plugins_url('/amoforms/js/core/fn.js'), array('jquery'));
wp_register_script('amoforms_email_settings', plugins_url('/amoforms/js/settings/email.js'), array(
	'jquery',
	'backbone',
	'amoforms_fn',
	'intl-tel-input',
));
wp_enqueue_script(
	'amoforms_email_page',
	plugins_url('/amoforms/js/settings/email_page.js'),
	array(
		'amoforms_email_settings',
	)
);
?>

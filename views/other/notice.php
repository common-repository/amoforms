<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
function amoforms_footer_notice() {
	$footer_text = sprintf( __( 'Please rate %samoForms%s %s on %sWordPress.org%s to let us know that it was useful to you and to help us to improve our product further. Thank you!', 'amoforms' ),
				'<strong>', '</strong>',
				'<a href="http://wordpress.org/support/view/plugin-reviews/amoforms?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
				'<a href="http://wordpress.org/support/view/plugin-reviews/amoforms?filter=5" target="_blank">', '</a>'
		);
	return $footer_text;
}
add_filter( 'admin_footer_text', 'amoforms_footer_notice' , 1, 0);
$type = $this->get('type');
$page = $this->get('page');
wp_enqueue_script(
		'amoforms_notice_settings',
		plugins_url('/amoforms/js/settings/notice.js'),
		array(
				'jquery',
		)
);
?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/app.css' ?>">
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/notices.css' ?>">
<?php if ($type == 'plugin') :?>
	<div class="amoforms_notice installed updated">
		<div class="amoforms__notice btn">
			<a href="<?php echo $page ?>">
				<button class="amoforms__notice-go-activation">Activate Your amoForms Plugin</button>
			</a>
		</div>
		<div class="amoforms__notice about">
			<p class="amoforms__notice-about">Almost done — activate amoForms plugin and create webforms fast and easy!</p>
		</div>
		<div class="amoforms__notice logo">
			<img src="<?php echo AMOFORMS_PLUGIN_URL . "/images/amoforms-logo.png" ?>" alt="Logo">
		</div>
	</div>
<?php elseif ($type == 'promo') :?>
	<div id="promo" class="amoforms_notice promo">
		<div class="amoforms__notice logo">
			<img src="<?php echo AMOFORMS_PLUGIN_URL . "/images/amoforms-logo.png" ?>" alt="Logo">
		</div>
		<div class="amoforms__notice about">
			<p class="title">How's It Going?</p>
			<p class="text">Thank you for using amoForms! We hope that you've found everything you need, but if you have any questions:</p>
			<ul class="links">
				<li><i class="p-icon docs"></i><a href="">Check out our documentation</a></li>
				<li><i class="p-icon help"></i><a href="">Get some help</a></li>
				<li><i class="p-icon demo"></i><a href="">Request a demo</a></li>
				<li><i class="p-icon dismiss"></i><a data-action="dismiss" href="#">Dismiss</a></li>
			</ul>
		</div>
	</div>
<?php elseif ($type == 'review') :?>
<div id="review" class="amoforms_notice review updated">
	<div class="amoforms__notice logo">
		<img src="<?php echo AMOFORMS_PLUGIN_URL . "/images/amoforms-logo.png" ?>" alt="Logo">
	</div>`
	<div class="amoforms__notice about">
		<p class="title">Leave a Review?</p>
		<p class="text">We hope you enjoyed using amoForms! Would you consider leaving us a review on Wordpress.org?</p>
		<ul class="links">
			<li><i class="p-icon already"></i><a data-action="dismiss" href="#">I've already left a review</a></li>
			<li><i class="p-icon later"></i><a data-action="suspend" href="#">Maybe later</a></li>
			<li><i class="p-icon sure"></i><a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/amoforms">Sure! I'd love to!</a></li>
		</ul>
	</div>
	<div class="close">
		<a data-action="dismiss" href="#">
			<i class="p-icon close"></i>
		</a>
	</div>
</div>
<?php endif;?>


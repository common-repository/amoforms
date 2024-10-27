<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

wp_register_script('erroneous', plugins_url('/amoforms/js/vendor/erroneous/erroneous.js'));
wp_enqueue_script('amoforms_errors', plugins_url('/amoforms/js/core/errors.js'), ['erroneous']);
?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/app.css' ?>">

<div class="amoforms amoforms-test" id="amoforms">
	<?php
	if (!$this->get('hide_top_nav')) {
		$this->render('settings/top_nav');
	}
	$this->render('other/notice');
	?>
	<div class="amoforms__content" id="amoforms_content">
		<?php $this->render($this->get('path')); ?>
	</div>
</div>

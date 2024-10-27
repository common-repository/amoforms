<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
?>
<link rel="stylesheet" href="<?php echo AMOFORMS_CSS_URL . '/entries.css' ?>">

<div class="amoforms amoforms-test" id="amoforms">
	<?php //$this->render('settings/top_nav'); ?>
	<div class="amoforms__content" id="amoforms_content">
		<?php $this->render($this->get('path')); ?>
	</div>
</div>

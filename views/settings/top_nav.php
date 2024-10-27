<?php defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
/** @var Amoforms\Views\Interfaces\Base $this */
/** @var \Amoforms\Models\Forms\Interfaces\Form $form */
if (!$form = $this->get('form')) {
	throw new \Amoforms\Views\Exceptions\Runtime('Form not exists');
}
$router = \Amoforms\Router::instance();
$current_page = !empty($_GET['page']) ? $_GET['page'] : FALSE;
$current_action = $router->get_action();
?>
<div class="amoforms__top-nav">
	<?php foreach ($router->get_top_menu_urls($form->id()) as $item):?>
		<a href="<?php echo $router->get_settings_page_url($item['action'], $form->id()) ?>">
			<div class="amoforms__top-nav__item <?php echo $item['action'] === $current_action ? 'active' : '' ?>"><?php echo $item['name'] ?></div>
		</a>
	<?php endforeach; ?>
	<?php if($current_action == 'form_preview'): ?>
		<a target="_blank" href="<?php echo $router->get_preview_page_url($form->id())?>">
			<div class="amoforms__button_preview">On-Page Preview</div>
		</a>
	<?php endif; ?>
</div>

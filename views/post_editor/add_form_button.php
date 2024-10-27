<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/** @var array $forms */
$forms = $this->get('forms');
?>

<div id="amoforms-add-form-modal" class="amoforms-add-form-modal" style="display: none">
	<div class="amoforms-add-form-modal__panel__title">
		<span class="amoforms-add-form-modal__panel__title-text">Add Form</span>
	</div>
	<div class="amoforms-add-form-modal__panel">
		<label for="amoforms-form-select">Select a form</label>
		<select name="amoforms-form-select" id="amoforms-form-select">
			<?php foreach ($forms as $form) { ?>
				<option value="<?php echo $form['id'] ?>"><?php echo $form['name'] ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="amoforms-add-form-modal__footer clearfix">
		<button id="amoforms-select-form-button-ok" class="amoforms-add-form-modal__footer_button--ok">OK</button>
		<button id="amoforms-select-form-button-cancel" class="amoforms-add-form-modal__footer_button--cancel">Cancel</button>
	</div>
</div>

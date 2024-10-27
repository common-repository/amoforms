<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
$columns = $this->get('columns');
$entries = $this->get('entries');
/** @var \Amoforms\Models\Forms\Interfaces\Collection $forms */
$forms = $this->get('forms');
$form_id = $this->get('form_id');
$page = $this->get('page');
?>

<div class="wrap amoforms-entries-page">
	<h2>Entries</h2>
	<div class="amoforms-entries-page-form-select-wrapper">
		<form action="/wp-admin/admin.php" method="get">
			<input type="hidden" name="page" value="amoforms-entries">
			<select name="filter[form_id]" id="form_select" title="Form select">
				<option value="0">All forms</option>
				<?php /** @var \Amoforms\Models\Forms\Interfaces\Form $form */
				foreach ($forms as $form) { ?>
					<option value="<?php echo $form->id() ?>" <?php echo ($form->id() === $form_id) ? 'selected' : '' ?>><?php echo $form->get('name') ?></option>
				<?php } ?>
			</select>
			<input type="submit" value="Show">
		</form>
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr>
			<?php foreach ($columns as $key => $name): ?>
			<th><?php echo $name ?></th>
			<?php endforeach ?>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($entries as $entry) { ?>
			<tr>
				<?php foreach ($columns as $key => $name): ?>
				<td>
					<?php if ($key === 'id') { ?>
						<a href="/wp-admin/admin.php?page=amoforms-entries&action=detail&id=<?php echo $entry[$key] ?>">Entry #<?php echo $entry[$key] ?></a>
					<?php } else {
						echo $entry[$key];
					} ?>
				</td>
				<?php endforeach ?>
			</tr>
		<?php } ?>
		</tbody>
	</table>

	<div class="amoforms_pagination">
		Page <?php echo $page['current'] ?> of <?php echo $page['total'] ?: 1 ?>
		<div class="amoforms_pagination_buttons_wrapper">
			<?php if ($page['current'] > 1) { ?>
				<a href="/wp-admin/admin.php?page=amoforms-entries&filter[form_id]=<?php echo $form_id?>&page_n=<?php echo $page['current'] - 1 ?>">Previous</a>
			<?php } if ($page['current'] > 1 && $page['current'] < $page['total']) { ?>
				|
			<?php } if ($page['current'] < $page['total']) { ?>
				<a href="/wp-admin/admin.php?page=amoforms-entries&filter[form_id]=<?php echo $form_id?>&page_n=<?php echo $page['current'] + 1 ?>">Next</a>
			<?php } ?>
		</div>
	</div>
</div>

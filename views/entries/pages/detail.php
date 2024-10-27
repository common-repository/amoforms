<?php
/** @var Amoforms\Views\Interfaces\Base $this */
use Amoforms\Models\Fields\Types\Base_Field;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
$entry = $this->get('entry');
$info = $this->get('info');
?>

<div class="wrap amoforms-entries-page amoforms-entry-detail-page">

	<div class="amoforms-entry-detail-info-block">
		<h2>Entry #<?php echo $entry['id'] ?></h2>
		<table class="wp-list-table widefat striped amoforms-entry-detail-table">
			<?php foreach ($info as $name => $value) { ?>
				<tr>
					<th><?php echo $name ?></th>
					<td><?php echo $value ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>

	<div class="amoforms-entry-detail-info-block">
		<h2>Fields</h2>
		<table class="wp-list-table widefat striped amoforms-entry-detail-table">
			<?php foreach ($entry['fields'] as $field): ?>
				<tr>
					<th><?php echo $field['name'] ?></th>
					<td>
						<?php
						if ($field['type'] === Base_Field::TYPE_FILE) {
							if (!is_array($field['value'])) {
								continue;
							}

							foreach ($field['value'] as $value): ?>
								<a href="<?php echo $value['url'] ?>"><?php echo $value['name'] ?></a><br>
							<?php endforeach;
						} else {
							echo $field['value'];
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>

	<div class="amoforms-entry-detail-footer">
		<a href="/wp-admin/admin.php?page=amoforms-entries">Back to list</a>
	</div>
</div>


<?php
/** @var Amoforms\Views\Interfaces\Base $this */
defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');
if ($text = $this->get('text')) {
	echo '<p>' . $this->get('text') . '</p>';
}
?>
<script>window.location.replace('<?php echo $this->get('url') ?>')</script>

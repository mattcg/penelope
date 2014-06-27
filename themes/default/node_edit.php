<main class="node edit">
	<?php

	require __path('node_header.php');

	if (!empty($error)) {

	?>
	<p class="error">Error while updating node: <?php __($error->getMessage()); ?></p>
	<?php

	}

	?>

	<form class="update node node-<?php __class($node_schema->getName()); ?>" action="<?php __($node->getPath()); ?>" method="POST">
		<?php

		foreach ($properties as $property) {
			$property_id = __class('edit-node-property-' . $property->getName(), false);

			require __DIR__ . '/property_label.php';
			require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
			require __DIR__ . '/property_error.php';
		}

		?>
		<input type="hidden" name="_METHOD" value="PUT">
		<hr>
		<input type="submit" value="Update">
	</form>

	<form class="delete node node-<?php __class($node_schema->getName()); ?>" action="<?php __($node->getPath()); ?>" method="POST">
		<input type="hidden" name="_METHOD" value="DELETE">
		<input type="submit" value="Delete">
	</form>
</main>

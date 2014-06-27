<main class="node new">
	<?php

	require __path('node_header.php');

	if (!empty($error)) {

	?>
	<p class="error">Error while creating node: <?php __($error->getMessage()); ?></p>
	<?php

	}

	?>

	<form class="create node node-<?php __class($node_schema->getName()); ?>" action="<?php __($node_schema->getCollectionPath()); ?>" method="POST">
		<?php

		foreach ($properties as $property) {
			$property_id = __class('new-node-property-' . $property->getName(), false);

			require __DIR__ . '/property_label.php';
			require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
			require __DIR__ . '/property_error.php';
		}

		?>
		<hr>
		<input type="submit" value="Create">
	</form>
</main>

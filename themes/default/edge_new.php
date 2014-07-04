<main class="new object edge">
	<article class="object edge <?php __class('edge-' . $edge_schema->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="body">
			<?php

			if (!empty($error)) {

			?>
			<p class="error">Error while creating relationship: <?php __($error->getMessage()); ?></p>
			<?php

			}

			?>
			<form enctype="<?php __formenc($edge_schema); ?>" class="create object edge <?php __class('edge-' . $edge_schema->getName()); ?>" action="<?php __($edge_schema->getCollectionPath()); ?>" method="POST">
				<?php

				foreach ($properties as $property) {
					$property_id = __class('new-edge-property-' . $property->getName(), false);

					require __DIR__ . '/property_label.php';
					require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
					require __DIR__ . '/property_error.php';
				}

				?>
				<hr>
				<input type="submit" value="Create">
			</form>
		</div>
	</article>
</main>

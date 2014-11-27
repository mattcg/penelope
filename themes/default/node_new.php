<main class="new object node">
	<article class="object node <?php __class('node-' . $node_schema->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="main-body object node">
			<?php

			if (!empty($error)) {

			?>
			<p class="error"><?php __(_m('create_node_error', $error->getMessage())); ?></p>
			<?php

			}

			?>
			<form enctype="<?php __formenc($node_schema); ?>" class="create object node <?php __class('node-' . $node_schema->getName()); ?>" action="<?php __(_e($node_schema->getCollectionPath())); ?>" method="POST">
				<?php

				foreach ($properties as $property) {
					$property_id = __class('new-node-property-' . $property->getName(), false);

					require __DIR__ . '/property_label.php';
					require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
					require __DIR__ . '/property_error.php';
				}

				?>
				<hr>
				<input class="crud-button create" type="submit" value="<?php __(_m('create_node_button_text')); ?>">
			</form>
		</div>
	</article>
</main>

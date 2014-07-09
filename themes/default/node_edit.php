<main class="edit object node">
	<article class="object node <?php __class('node-' . $node_schema->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="body">
			<?php

			if (!empty($error)) {

			?>
			<p class="error"><?php __(_m('update_node_error', $error->getMessage())); ?></p>
			<?php

			}

			?>
			<form enctype="<?php __formenc($node_schema); ?>" class="update object node <?php __class('node-' . $node_schema->getName()); ?>" action="<?php __($node->getPath()); ?>" method="POST">
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
				<input type="submit" value="<?php __(_m('update_node_button_text')); ?>">
			</form>

			<form class="delete object node <?php __class('node-' . $node_schema->getName()); ?>" action="<?php __($node->getPath()); ?>" method="POST">
				<input type="hidden" name="_METHOD" value="DELETE">
				<input type="submit" value="<?php __(_m('delete_node_button_text')); ?>">
			</form>
		</div>
	</article>
</main>

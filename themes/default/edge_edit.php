<main class="edit object edge">
	<article class="object edge <?php __class('edge-' . $edge_schema->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="main-body object edge">
			<?php

			if (!empty($error)) {

			?>
			<p class="error"><?php __(_m('update_edge_error', $error->getMessage())); ?></p>
			<?php

			}

			?>
			<form enctype="<?php __formenc($edge_schema); ?>" class="update object edge <?php __class('edge-'. $edge_schema->getName()); ?>" action="<?php __(_e($edge->getPath())); ?>" method="POST">
				<?php

				foreach ($properties as $property) {
					$property_id = __class('edit-edge-property-' . $property->getName(), false);

					require __DIR__ . '/property_label.php';
					require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
					require __DIR__ . '/property_error.php';
				}

				?>
				<input type="hidden" name="_METHOD" value="PUT">
				<hr>
				<input class="crud-button" type="submit" value="<?php __(_m('update_edge_button_text')); ?>">
			</form>

			<form class="delete object edge <?php __class('edge-' . $edge_schema->getName()); ?>" action="<?php __(_e($edge->getPath())); ?>" method="POST" data-confirm="<?php __(_m('delete_edge_confirm', $edge->getTitle())); ?>">
				<input type="hidden" name="_METHOD" value="DELETE">
				<input class="crud-button delete" type="submit" value="<?php __(_m('delete_edge_button_text')); ?>">
			</form>
		</div>
	</article>
</main>

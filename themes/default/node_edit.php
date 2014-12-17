<main class="edit object node">
	<article class="object node <?php __class('node-' . $node_schema->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="main-body object node">
			<?php

			if (!empty($error)) {

			?>
			<p class="error"><?php __(_m('update_node_error', $error->getMessage())); ?></p>
			<?php

			}

			if (!empty($properties)) {

			?>
			<form enctype="<?php __formenc($node_schema); ?>" class="update object node <?php __class('node-' . $node_schema->getName()); ?>" action="<?php __(_e($node->getPath())); ?>" method="POST">
				<?php

				foreach ($properties as $property) {
					require __path('property_edit.php');
				}

				?>
				<input type="hidden" name="_METHOD" value="PUT">
				<hr>
				<input class="crud-button update" type="submit" value="<?php __(_m('update_node_button_text')); ?>">
			</form>
			<?php

			}

			?>
			<form class="delete object node <?php __class('node-' . $node_schema->getName()); ?>" action="<?php __(_e($node->getPath())); ?>" method="POST" data-confirm="<?php __(_m('delete_node_confirm', $node->getTitle())); ?>">
				<input type="hidden" name="_METHOD" value="DELETE">
				<input class="crud-button delete" type="submit" value="<?php __(_m('delete_node_button_text')); ?>">
			</form>
		</div>
	</article>
</main>

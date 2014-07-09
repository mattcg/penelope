<main class="new object edge">
	<article class="object edge <?php __class('edge-' . $edge_schema->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="body">
			<?php

			if (!empty($error)) {

			?>
			<p class="error"><?php __(_m('create_edge_error', $error->getMessage())); ?></p>
			<?php

			}

			?>
			<form enctype="<?php __formenc($edge_schema); ?>" class="create object edge <?php __class('edge-' . $edge_schema->getName()); ?>" action="<?php __($node->getEdgeCollectionPath($edge_schema)); ?>" method="POST">
				<?php

				foreach ($properties as $property) {
					$property_id = __class('new-edge-property-' . $property->getName(), false);

					require __DIR__ . '/property_label.php';
					require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
					require __DIR__ . '/property_error.php';
				}

				?>
				<label for="node-collection"><?php __(_m('edge_to_label')); ?></label>
				<select name="to_node">
					<?php

					foreach ($node->getCollection() as $to_node) {

						// A relationship to itself would be pointless.
						if ($to_node->getId() === $node->getId()) {
							continue;
						}

					?>
					<option value="<?php __($to_node->getId()); ?>"><?php __($to_node->getTitle()); ?></option>
					<?php

					}

					?>
				</select>
				<hr>
				<input type="submit" value="<?php __(_m('create_edge_button_text')); ?>">
			</form>
		</div>
	</article>
</main>

<main class="new object edge">
	<article class="object edge <?php __class('edge-' . $edge_schema->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="main-body object edge">
			<?php

			if (!empty($error)) {

			?>
			<p class="error"><?php __(_m('create_edge_error', $error->getMessage())); ?></p>
			<?php

			}

			if (empty($end_nodes)) {

			?>
			<p><?php __(_m('edge_to_none', $edge_schema->getEndNodeSchema()->getDisplayName(), $edge_schema->getEndNodeSchema()->getNewPath(), _m('new_node_title', $edge_schema->getEndNodeSchema()->getDisplayName()))); ?></p>
			<?php

			} else {

			?>
			<form enctype="<?php __formenc($edge_schema); ?>" class="create object edge <?php __class('edge-' . $edge_schema->getName()); ?>" action="<?php __(_e($node->getEdgeCollectionPath($edge_schema))); ?>" method="POST">
				<?php

				foreach ($properties as $property) {
					$property_id = __class('new-edge-property-' . $property->getName(), false);

					require __DIR__ . '/property_label.php';
					require __DIR__ . '/types/' . $property->getSchema()->getType() . '_input.php';
					require __DIR__ . '/property_error.php';
				}

				?>
				<label for="node-collection"><?php __(_m('edge_to_label')); ?></label>
				<select name="end_node">
					<?php

					foreach ($end_nodes as $end_node) {

						// A relationship to itself would be pointless.
						if ($end_node->getId() === $node->getId()) {
							continue;
						}

					?>
					<option value="<?php __(_e($end_node->getId())); ?>"><?php __(_e($end_node->getTitle())); ?></option>
					<?php

					}

					?>
				</select>
				<hr>
				<input class="crud-button create" type="submit" value="<?php __(_m('create_edge_button_text')); ?>">
			</form>
			<?php

			}

			?>
		</div>
	</article>
</main>

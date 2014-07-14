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
				<?php

				// A relationship to itself would be pointless.
				$to_nodes = array_filter($node->getCollection(), function($to_node) use ($node) {
					return $to_node->getId() !== $node->getId();
				});

				if (empty($to_nodes)) {

				?>
				<p><?php __(_m('edge_to_none', $node->getSchema()->getName(), $node->getSchema()->getNewPath(), _m('new_node_title', $node->getSchema()->getName()))); ?></p>
				<?php

				} else {

				?>
				<select name="to_node">
					<?php

					foreach ($to_nodes as $to_node) {

					?>
					<option value="<?php __($to_node->getId()); ?>"><?php __($to_node->getTitle()); ?></option>
					<?php

					}

					?>
				</select>
				<?php

				}

				?>
				<hr>
				<input type="submit" value="<?php __(_m('create_edge_button_text')); ?>">
			</form>
		</div>
	</article>
</main>

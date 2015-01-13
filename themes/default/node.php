<main class="view object node">
	<article class="object node <?php __class('node-' . $node->getSchema()->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="main-body object node">
			<?php

			$option = $node_schema->getOption('format.body');
			if ($option and $property = $node->getProperty($option) and $property->hasValue()) {

			?>
			<div class="object-body">
			<?php

				require __path('types/' . $property->getSchema()->getType() . '.php');

			?>
			</div>
			<?php

			}

			$hidden_properties = array();
			if ($node_schema->hasOption('format.abstract')) {
				$hidden_properties[] = $node_schema->getOption('format.abstract');
			}

			if ($node_schema->hasOption('format.body')) {
				$hidden_properties[] = $node_schema->getOption('format.body');
			}

			if ($node_schema->hasOption('format.figure')) {
				$hidden_properties[] = $node_schema->getOption('format.figure');
			}

			$properties = array();
			foreach ($node->getProperties() as $property) {
				if (!in_array($property->getName(), $hidden_properties, true)) {
					$properties[] = $property;
				}
			}

			if (empty($properties)) {

			?>
			<p><?php __(_m('node_no_properties')); ?></p>
			<?php

			} else {

			?>
			<dl class="object-properties node-properties" title="<?php __(_m('object_properties_title')); ?>">
			<?php

				foreach ($properties as $property) {
					require __path('property.php');
				}

			?>
			</dl>
			<?php

			}

			?>
		</div>
		<footer class="main-footer object node">
			<section class="edges">
				<a name="<?php __(_m('node_edges_anchor')); ?>"><h1 class="section-title"><?php __(_m('node_edges_title')); ?></h1></a>
				<?php

				if (empty($edge_schemas)) {

				?>
				<p><?php __(_m('node_edges_none_defined')); ?></p>
				<?php

				} else if (!$has_edges) {

				?>
				<p><?php __(_m('node_edges_none')); ?></p>
				<?php

				}

				if (!empty($edge_schemas)) {

					foreach ($edge_schemas as $edge_schema) {

						$edge_schema_edges = $edges[$edge_schema->getName()];

				?>
				<section class="edge-schema<?php

					// Need to use count() as variable is an Iterator object and empty will always return false.
					if (0 === count($edge_schema_edges)) {
						__(' ' . __class('no-edges', false));
					}

					?>">
					<h2 class="edge-schema-title"><?php

					if (!$edge_schema->permitsStartNode($node_schema)) {
						__(_e($edge_schema->getOption('format.reverse_name')));
					} else {
						__(_e($edge_schema->getDisplayName(0)));
					}

					?></h2>
					<?php

						// Can only create a new edge from this node if it's allowed.
						if ($edge_schema->permitsStartNode($node_schema)) {

					?>
					<a class="crud-button new" href="<?php __(_e($node->getNewEdgePath($edge_schema))); ?>" title="<?php __(_a('new_edge_button_title', $edge_schema->getDisplayName(), $node->getTitle())); ?> "><?php __(_m('new_edge_button_text')); ?></a>
					<?php

						}

						if (!empty($edge_schema_edges)) {

					?>
					<nav class="collection edges <?php __class('edges-' . $edge_schema->getName()); ?>">
						<ul>
							<?php

							foreach ($edge_schema_edges as $edge) {

								// Switch based on context and direction, handling reverse edges.
								if ($node->getId() === $edge->getEndNode()->getId()) {
									$related_node = $edge->getStartNode();
								} else {
									$related_node = $edge->getEndNode();
								}

							?>
							<li class="object <?php __class('object-type-' . $related_node->getSchema()->getName()); ?>">
								<a class="object-link" href="<?php __(_e($related_node->getPath())); ?>" title="<?php __(_s($related_node->getTitle())); ?>">
									<h1 class="object-title"><?php __(_e($related_node->getTitle())); ?></h1>
									<h2 class="object-subtitle"><?php __(_e($related_node->getSchema()->getDisplayName())); ?></h2>
								</a>
								<a class="edge-link" href="<?php __(_e($edge->getPath())); ?>" title="<?php __(_s($edge->getTitle())); ?>"><?php __(_m('node_edge_link_title')); ?></a>
							</li>
							<?php

							}

							?>
						</ul>
					</nav>
					<?php

						}

					?>
				</section>
				<?php

					}

				?>
			</section>
			<?php

			}

			?>
		</footer>
	</article>
</main>

<main class="view object node">
	<article class="object node <?php __class('node-' . $node->getSchema()->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="main-body object node">
			<?php

			if (isset($body_property_name)) {
				$property = $node->getProperty($body_property_name);
				if ($property->hasValue()) {

			?>
			<div class="object-body">
			<?php

				require __path('types/' . $property->getSchema()->getType() . '.php');

			?>
			</div>
			<?php

				}
			}

			?>
			<dl class="object-properties node-properties" title="<?php __(_m('object_properties_title')); ?>">
			<?php

			foreach ($node->getProperties() as $property) {
				if (isset($abstract_property_name) and $abstract_property_name === $property->getName()) {
					continue;
				}

				if (isset($body_property_name) and $body_property_name === $property->getName()) {
					continue;
				}

				require __path('property.php');
			}

			?>
			</dl>
		</div>
		<footer class="main-footer object node">
			<section class="edges">
				<a name="edges"><h1 class="section-title"><?php __(_m('node_edges_title')); ?></h1></a>
				<?php

				if (empty($edge_schemas) and empty($reverse_edge_schemas)) {

				?>
				<p><?php __(_m('node_edges_none_defined')); ?></p>
				<?php

				} else {

					if (!$has_edges) {

					?>
				<p><?php __(_m('node_edges_none')); ?></p>
					<?php

					}

					// Sort by title using Unicode Collation Algorithm rules.
					$collator = \Collator::create('root');
					$collator_sorter = function($a, $b) use ($collator, $node) {

						// Must switch based on the context and direction, as the array may include reverse edges.
						if ($node->getId() === $a->getEndNode()->getId()) {
							$a_title = $a->getStartNode()->getTitle();
						} else {
							$a_title = $a->getEndNode()->getTitle();
						}

						if ($node->getId() === $b->getEndNode()->getId()) {
							$b_title = $b->getStartNode()->getTitle();
						} else {
							$b_title = $b->getEndNode()->getTitle();
						}

						return $collator->compare($a_title, $b_title);
					};

					if (!empty($edge_schemas)) {

						foreach ($edge_schemas as $edge_schema) {

							$edge_schema_edges = $edges[$edge_schema->getName()];

							// Munge reverse edges into the same list if the reverse relationship uses the same name.
							if ($edge_schema->getOption('format.reverse_name') === $edge_schema->getDisplayName() and isset($reverse_edges[$edge_schema->getName()])) {
								$edge_schema_edges += $reverse_edges[$edge_schema->getName()];
							}

				?>
				<h2 class="edge-schema-title<?php

				if (empty($edge_schema_edges)) {
					__(' ' . __class('no-edges', false));
				}

				?>"><?php __(_e($edge_schema->getDisplayName(0))); ?></h2>
				<nav class="collection edges <?php __class('edges-' . $edge_schema->getName()); ?>">
					<ul>
						<li class="new"><a class="button new" href="<?php __(_e($node->getNewEdgePath($edge_schema))); ?>" title="<?php __(_a('new_edge_button_title', $edge_schema->getDisplayName(), $node->getTitle())); ?> "><?php __(_m('new_edge_button_text')); ?></a></li>
						<?php

							usort($edge_schema_edges, $collator_sorter);

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

					}

					if (!empty($reverse_edge_schemas)) {

						foreach ($reverse_edge_schemas as $reverse_edge_schema) {

							// Exclude edges munged into the previous list.
							if ($reverse_edge_schema->getOption('format.reverse_name') === $reverse_edge_schema->getDisplayName() and isset($edges[$reverse_edge_schema->getName()])) {
								continue;
							}

							$reverse_edge_schema_edges = $reverse_edges[$reverse_edge_schema->getName()];

				?>
				<h2 class="edge-schema-title<?php

				if (empty($edge_schema_edges)) {
					__(' ' . __class('no-edges', false));
				}

				?>"><?php __(_e($reverse_edge_schema->getOption('format.reverse_name'))); ?></h2>
				<nav class="collection edges reverse-edges <?php __class('edges-' . $reverse_edge_schema->getName()); ?>">
					<ul>
						<?php

							usort($reverse_edge_schema_edges, $collator_sorter);

							foreach ($reverse_edge_schema_edges as $reverse_edge) {
								$related_node = $reverse_edge->getStartNode();

						?>
						<li class="object <?php __class('object-type-' . $related_node->getSchema()->getName()); ?>">
							<a class="object-link" href="<?php __(_e($related_node->getPath())); ?>" title="<?php __(_s($related_node->getTitle())); ?>">
								<h1 class="object-title"><?php __(_e($related_node->getTitle())); ?></h1>
								<h2 class="object-subtitle"><?php __(_e($related_node->getSchema()->getDisplayName())); ?></h2>
							</a>
							<a class="edge-link" href="<?php __(_e($reverse_edge->getPath())); ?>" title="<?php __(_s($reverse_edge->getTitle())); ?>"><?php __(_m('node_edge_link_title')); ?></a>
						</li>
						<?php

						}

						?>
					</ul>
				</nav>
				<?php
						}

					}

				}

				?>
			</section>
		</footer>
	</article>
</main>

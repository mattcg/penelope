<main class="object node">
	<article class="object node <?php __class('node-' . $node->getSchema()->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="body">
			<dl class="object-properties node-properties">
			<?php

			foreach ($node->getProperties() as $property) {
				require 'property.php';
			}

			?>
			</dl>
		</div>
		<footer class="object node">
			<section class="edges">
				<h1><?php __(_m('node_edges_title')); ?></h1>
				<?php

				if (empty($edge_schemas)) {

				?>
				<p><?php __(_m('node_edges_none_defined')); ?></p>
				<?php

				} else {

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

					foreach ($edge_schemas as $edge_schema) {

				?>
				<h2><?php __(_e($edge_schema->getDisplayName())); ?></h2>
				<nav class="collection edges <?php __class('edges-' . $edge_schema->getName()); ?>">
					<ul>
						<li class="new"><a class="button new" href="<?php __(_e($node->getNewEdgePath($edge_schema))); ?>" title="<?php __(_a('new_edge_button_title', $edge_schema->getDisplayName(), $node->getTitle())); ?> "><?php __(_m('new_edge_button_text')); ?></a></li>
						<?php

						$edge_schema_edges = $edges[$edge_schema->getName()];

						// Munge reverse edges into the same list if the reverse relationship uses the same name.
						if ($edge_schema->getOption('format.reverse_name') === $edge_schema->getDisplayName() and isset($reverse_edges[$edge_schema->getName()])) {
							$edge_schema_edges += $reverse_edges[$edge_schema->getName()];
						}

						usort($edge_schema_edges, $collator_sorter);

						foreach ($edge_schema_edges as $edge) {

						?>
						<li class="object"><a href="<?php __(_e($edge->getPath())); ?>" title="<?php __(_e($edge->getTitle())); ?>"><?php

						// Switch based on context and direction, handling reverse edges.
						if ($node->getId() === $edge->getEndNode()->getId()) {
							__(_e($edge->getStartNode()->getTitle()));
						} else {
							__(_e($edge->getEndNode()->getTitle()));
						}

						?></a></li>
						<?php

						}

						?>
					</ul>
				</nav>
				<?php

					}

					foreach ($reverse_edge_schemas as $reverse_edge_schema) {

						// Exclude edges munged into the previous list.
						if ($reverse_edge_schema->getOption('format.reverse_name') === $reverse_edge_schema->getDisplayName() and isset($edges[$reverse_edge_schema->getName()])) {
							continue;
						}

				?>
				<h2><?php __(_e($reverse_edge_schema->getOption('format.reverse_name'))); ?></h2>
				<nav class="collection edges reverse-edges <?php __class('edges-' . $reverse_edge_schema->getName()); ?>">
					<ul>
						<?php

						$reverse_edge_schema_edges = $reverse_edges[$reverse_edge_schema->getName()];

						usort($reverse_edge_schema_edges, $collator_sorter);

						foreach ($reverse_edge_schema_edges as $reverse_edge) {

						?>
						<li class="object"><a href="<?php __(_e($reverse_edge->getPath())); ?>" title="<?php __(_e($reverse_edge->getTitle())); ?>"><?php __(_e($reverse_edge->getStartNode()->getTitle())); ?></a></li>
						<?php

						}

						?>
					</ul>
				</nav>
				<?php

					}

				}

				?>
			</section>
		</footer>
	</article>
</main>

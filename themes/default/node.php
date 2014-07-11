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

					foreach ($edge_schemas as $edge_schema) {

				?>
				<h2><?php __($edge_schema->getName()); ?></h2>
				<nav class="collection edges <?php __class('edges-' . $edge_schema->getName()); ?>">
					<ul>
						<li class="new"><a class="button new" href="<?php __($node->getNewEdgePath($edge_schema)); ?>" title="<?php __(_m('new_edge_button_title', $edge_schema->getName(), $node->getTitle())); ?> "><?php __(_m('new_edge_button_text')); ?></a></li>
						<?php

						foreach ($node->getOutEdges($edge_schema) as $edge) {

						?>
						<li class="object"><a href="<?php __($edge->getPath()); ?>" title="<?php __($edge->getTitle()); ?>"><?php __($edge->getToNode()->getTitle()); ?></a></li>
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

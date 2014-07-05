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
				<h1>Relationships</h1>
				<?php

				foreach ($edge_schemas as $edge_schema) {

				?>
				<h2><?php __($edge_schema->getName()); ?></h2>
				<nav class="edges <?php __class('edges-' . $edge_schema->getName()); ?>">
					<ul>
						<li class="new"><a class="new" href="<?php __($node->getNewEdgePath($edge_schema)); ?>" title="Create a new <?php __($edge_schema->getName()); ?> relationship from <?php __($node->getTitle()); ?>">New</a></li>
						<?php

						foreach ($node->getOutEdges($edge_schema) as $edge) {

						?>
						<li><a href="<?php __($edge->getPath()); ?>" title="<?php __($edge->getTitle()); ?>"><?php __($edge->getTitle()); ?></a></li>
						<?php

						}

						?>
					</ul>
				</nav>
				<?php

				}

				?>
			</section>
		</footer>
	</article>
</main>

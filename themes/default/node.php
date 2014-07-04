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
					<?php

					foreach ($app->getSchema()->getOutEdges($node->getSchema()->getName()) as $edge_schema) {

					?>
					<nav class="edges <?php __class('edges-' . $edge_schema->getName()); ?>"
						<ul>
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

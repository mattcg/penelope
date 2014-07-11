<main class="object edge deleted">
	<article class="object edge <?php __class('edge-' . $edge_schema->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="body">
			<p><?php __(_m('view_edge_collection_link', $node->getEdgeCollectionPath($edge_schema), $edge_schema->getName())); ?></p>
		</div>
	</article>
</main>

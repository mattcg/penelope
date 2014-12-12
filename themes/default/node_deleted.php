<main class="object node deleted">
	<article class="object node <?php __class('node-' . $node_schema->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="main-body object node">
			<p><?php __(_m('view_node_collection_link', $node_schema->getCollectionPath(), $node_schema->getDisplayName())); ?></p>
		</div>
	</article>
</main>

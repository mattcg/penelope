<main class="object node created">
	<article class="object node <?php __class('node-' . $node->getSchema()->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="body">
			<p><?php __(_m('view_node_link', $node->getPath(), $node->getTitle())); ?></p>
		</div>
	</article>
</main>

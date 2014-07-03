<main class="node created">
	<article class="node node-<?php __class($node->getSchema()->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="body">
			<p>View <a href="<?php __($node->getPath()); ?>" title="<?php __($node->getTitle()); ?>"><?php __($node->getTitle()); ?></a>.</p>
		</div>
	</article>
</main>

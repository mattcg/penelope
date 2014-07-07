<main class="object edge deleted">
	<article class="object edge <?php __class('edge-' . $edge_schema->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="body">
			<p>See all <a href="<?php __($node->getEdgeCollectionPath($edge_schema)); ?>"><?php __($edge_schema->getName()); ?></a> relationships.</p>
		</div>
	</article>
</main>

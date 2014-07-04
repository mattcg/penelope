<main class="object node deleted">
	<article class="object node <?php __class('node-' . $node_schema->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>
		<div class="body">
			<p>See all <a href="<?php __($node_schema->getCollectionPath()); ?>"><?php __($node_schema->getName()); ?></a> nodes.</p>
		</div>
	</article>
</main>

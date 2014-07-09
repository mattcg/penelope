<main class="object edge created">
	<article class="object edge <?php __class('edge-' . $edge->getSchema()->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="body">
			<p><?php __(_m('penelope.view_edge_link', $edge->getPath(), $edge->getTitle())); ?></p>
		</div>
	</article>
</main>

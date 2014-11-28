<main class="view object edge">
	<article class="object edge <?php __class('edge-' . $edge->getSchema()->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="main-body">
			<dl class="object-properties edge-properties">
			<?php

			foreach ($edge->getProperties() as $property) {
				require 'property.php';
			}

			?>
			</dl>
		</div>
	</article>
</main>

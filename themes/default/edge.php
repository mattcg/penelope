<main class="object edge">
	<article class="object edge <?php __class('edge-' . $edge->getSchema()->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="body">
			<object class="svg edge-svg" data="<?php __(_e($edge->getSvgPath())); ?>" height="100" width="400" type="image/svg+xml"></object>
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

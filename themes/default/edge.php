<main class="view object edge">
	<article class="object edge <?php __class('edge-' . $edge->getSchema()->getName()); ?>">
		<?php

		require __path('edge_header.php');

		?>
		<div class="main-body">
			<?php

			$properties = $edge->getProperties();
			if (empty($properties)) {

			?>
			<p><?php __(_m('edge_no_properties')); ?></p>
			<?php

			} else {

			?>
			<dl class="object-properties edge-properties" title="<?php __(_m('object_properties_title')); ?>">
			<?php

				foreach ($properties as $property) {
					require 'property.php';
				}

			?>
			</dl>
			<?php

			}

			?>
		</div>
	</article>
</main>

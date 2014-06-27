<main class="node">
	<article class="node node-<?php __class($node->getSchema()->getName()); ?>">
		<?php

		require __path('node_header.php');

		?>

		<dl class="node-properties">
		<?php

		foreach ($node->getProperties() as $property) {
			if (!$property->hasValue()) {
				continue;
			}

			$type_class = 'type-' . $property->getSchema()->getType();
			if ($property->getSchema()->isMultiValue()) {
				$type_class .= ' multivalue';
			}

			?>
			<dt class="<?php __($type_class); ?>"><?php __label($property); ?></dt>
			<dd class="<?php __($type_class); ?>">
			<?php

			require __path('types/' . $property->getSchema()->getType() . '.php');

			?>
			</dd>
			<?php
		}

		?>
		</dl>
	</article>
</main>

<main class="collection nodes">
	<?php

	require __path('node_header.php');

	?>
	<div class="body">
		<?php

		if (empty($nodes)) {

		?>
		<p><?php __(_m('node_collection_empty', $node_schema->getName())); ?></p>
		<?php

		} else {

		?>
		<nav class="collection nodes">
			<ul>
				<?php

				foreach ($nodes as $node) {

				?>
				<li><a href="<?php __($node->getPath()); ?>" title="<?php __($node->getTitle()); ?>"><?php __($node->getTitle()); ?></a></li>
				<?php

				}

				?>
			</ul>
		</nav>
		<?php

		}

		?>
	</div>
</main>

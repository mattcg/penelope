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
				<li class="object"><a href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>"><?php __(_e($node->getTitle())); ?></a></li>
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

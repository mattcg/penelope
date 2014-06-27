<main class="nodes">
	<?php

	require __path('node_header.php');

	?>

	<?php

	if (empty($nodes)) {

	?>
	<p>There aren't any <?php __($node_schema->getName()); ?> nodes.</p>
	<?php

	}

	?>

	<?php

	if (!empty($nodes)) {

	?>
	<nav class="nodes">
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
</main>

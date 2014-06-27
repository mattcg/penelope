<header class="node">
	<h1><?php __($title); ?></h1>
	<nav class="crud">
		<ul>
			<?php

			$schema_name = $node_schema->getName();

			?>
			<li class="new"><a class="new" href="<?php __($node_schema->getNewPath()); ?>" title="Create a new <?php __($schema_name); ?>">New</a></li>
			<?php

			if (isset($node)) {

			?>

			<li class="edit"><a class="edit" href="<?php __($node->getEditPath()); ?>" title="Edit <?php __($node->getTitle()); ?>">Edit</a></li>
			<?php

			}

			?>
			<li class="collection"><a class="collection" href="<?php __($node_schema->getCollectionPath()); ?>" title="<?php __($schema_name); ?> list">View all</a></li>
		</ul>
	</nav>
</header>

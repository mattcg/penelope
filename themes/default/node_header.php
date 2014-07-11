<header class="object node">
	<h1><?php __($title); ?></h1>
	<nav class="crud">
		<ul>
			<?php

			if (!isset($node_schema)) {
				$node_schema = $node->getSchema();
			}

			$schema_name = $node_schema->getName();

			?>
			<li class="new"><a class="button new" href="<?php __($node_schema->getNewPath()); ?>" title="Create a new <?php __($schema_name); ?>">New</a></li>
			<?php

			if (isset($node)) {

			?>
			<li class="edit"><a class="button edit" href="<?php __($node->getEditPath()); ?>" title="Edit <?php __($node->getTitle()); ?>">Edit</a></li>
			<li class="view"><a class="button view" href="<?php __($node->getPath()); ?>" title="View <?php __($node->getTitle()); ?>">View</a></li>
			<?php

			}

			?>
			<li class="collection"><a class="button collection" href="<?php __($node_schema->getCollectionPath()); ?>" title="<?php __($schema_name); ?> list">View all</a></li>
		</ul>
	</nav>
</header>

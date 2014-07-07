<header class="object edge">
	<h1><?php __($title); ?></h1>
	<nav class="crud">
		<ul>
			<?php

			if (!isset($edge_schema)) {
				$edge_schema = $edge->getSchema();
			}

			$schema_name = $edge_schema->getName();

			?>
			<li class="new"><a class="new" href="<?php __($node->getNewEdgePath($edge_schema)); ?>" title="Create a new <?php __($schema_name); ?> relationship from <?php __($node->getTitle()); ?>">New</a></li>
			<?php

			if (isset($edge)) {

			?>
			<li class="edit"><a class="edit" href="<?php __($edge->getEditPath()); ?>" title="Edit <?php __($edge->getTitle()); ?>">Edit</a></li>
			<li class="view"><a class="view" href="<?php __($edge->getPath()); ?>" title="View <?php __($edge->getTitle()); ?>">View</a></li>
			<?php

			}

			?>
			<li class="collection"><a class="collection" href="<?php __($node->getEdgeCollectionPath($edge_schema)); ?>" title="<?php __($schema_name); ?> list">View all</a></li>
			<li class="view-parent"><a class="view-parent" href="<?php __($node->getPath()); ?>" title="<?php __($node->getTitle()); ?>">Back to <?php __($node->getSchema()->getName()); ?></a></li>
		</ul>
	</nav>
</header>

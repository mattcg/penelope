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
			<li class="collection"><a class="collection" href="<?php __($node->getEdgeCollectionPath($edge_schema)); ?>" title="<?php __($schema_name); ?> list">View all</a></li>
		</ul>
	</nav>
</header>

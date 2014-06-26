<header>
	<h1><?php __($title); ?></h1>
	<nav class="crud">
		<ul>
			<?php

			$schema_name = $node_schema->getName();

			if (isset($node_uris['new'])) {

			?>
			<li><a class="new" href="<?php __($node_uris['new']); ?>" title="Create a new <?php __($schema_name); ?>">New</a></li>
			<?php

			}

			if (isset($node_uris['edit'])) {

			?>
			
			<li><a class="edit" href="<?php __($node_uris['edit']); ?>" title="Edit <?php __($schema_name); ?> #<?php __($node->getId()); ?>">Edit</a></li>
			<?php

			}

			if (isset($node_uris['collection'])) {

			?>
			<li><a class="collection" href="<?php __($node_uris['collection']); ?>" title="<?php __($schema_name); ?> list">View all</a></li>
			<?php

			}

			?>
		</ul>
	</nav>
</header>

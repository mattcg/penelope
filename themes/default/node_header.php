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
			<li class="new"><a class="button new" href="<?php __($node_schema->getNewPath()); ?>" title="<?php __(_m('new_node_button_title', $schema_name)); ?>"><?php __(_m('new_node_button_text')) ?></a></li>
			<?php

			if (isset($node)) {

			?>
			<li class="edit"><a class="button edit" href="<?php __($node->getEditPath()); ?>" title="<?php __(_m('edit_node_button_title', $node->getTitle())); ?>"><?php __(_m('edit_node_button_text')); ?></a></li>
			<li class="view"><a class="button view" href="<?php __($node->getPath()); ?>" title="<?php __(_m('view_node_button_title', $node->getTitle())); ?>"><?php __(_m('view_node_button_text')); ?></a></li>
			<?php

			}

			?>
			<li class="collection"><a class="button collection" href="<?php __($node_schema->getCollectionPath()); ?>" title="<?php __(_m('view_node_collection_button_title', $schema_name)); ?>"><?php __(_m('view_node_collection_button_text')); ?></a></li>
		</ul>
	</nav>
</header>

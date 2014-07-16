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
			<li class="new"><a class="button new" href="<?php __(_e($node->getNewEdgePath($edge_schema))); ?>" title="<?php __(_a('new_edge_button_title', $schema_name, $node->getTitle())); ?>"><?php __(_m('new_edge_button_text')); ?></a></li>
			<?php

			if (isset($edge)) {

			?>
			<li class="edit"><a class="button edit" href="<?php __(_e($edge->getEditPath())); ?>" title="<?php __(_a('edit_edge_button_title', $edge->getTitle())); ?>"><?php __(_m('edit_edge_button_text')); ?></a></li>
			<li class="view"><a class="view" href="<?php __(_e($edge->getPath())); ?>" title="<?php __(_a('view_edge_button_title')); ?>"><?php __(_m('view_edge_button_text')); ?></a></li>
			<?php

			}

			?>
			<li class="collection"><a class="button collection" href="<?php __(_e($node->getEdgeCollectionPath($edge_schema))); ?>" title="<?php __(_a('view_edge_collection_button_title', $schema_name)); ?>"><?php __(_m('view_edge_collection_button_text')); ?></a></li>
			<li class="view-parent"><a class="button view-parent" href="<?php __(_e($node->getPath())); ?>" title="<?php __(_a('edge_parent_button_title', $node->getTitle())); ?>"><?php __(_m('edge_parent_button_text', $node->getSchema()->getName())); ?></a></li>
		</ul>
	</nav>
</header>

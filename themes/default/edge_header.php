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
			<li class="new"><a class="new" href="<?php __($node->getNewEdgePath($edge_schema)); ?>" title="<?php __(_m('new_edge_title', $schema_name, $node->getTitle())); ?>"><?php __(_m('new_edge_link_text')); ?></a></li>
			<?php

			if (isset($edge)) {

			?>
			<li class="edit"><a class="edit" href="<?php __($edge->getEditPath()); ?>" title="<?php __(_m('edit_edge_title', $edge->getTitle())); ?>"><?php __(_m('edit_edge_link_text')); ?></a></li>
			<li class="view"><a class="view" href="<?php __($edge->getPath()); ?>" title="<?php __(_m('view_edge_title')); ?>"><?php __(_m('view_edge_link_text')); ?></a></li>
			<?php

			}

			?>
			<li class="collection"><a class="collection" href="<?php __($node->getEdgeCollectionPath($edge_schema)); ?>" title="<?php __(_m('view_edge_collection_title', $schema_name)); ?>"><?php __(_m('view_edge_collection_link_text')); ?></a></li>
			<li class="view-parent"><a class="view-parent" href="<?php __($node->getPath()); ?>" title="<?php __(_m('edge_parent_link_title', $node->getTitle())); ?>"><?php __(_m('edge_parent_link_text', $node->getSchema()->getName())); ?></a></li>
		</ul>
	</nav>
</header>

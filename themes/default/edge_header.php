<header class="main-header object edge">
	<?php

	if (!isset($edge_schema)) {
		$edge_schema = $edge->getSchema();
	}

	if (!isset($node_schema)) {
		$node_schema = $node->getSchema();
	}

	?>
	<nav class="breadcrumb" itemprop="breadcrumb">
		<a class="main-type" href="<?php __(_e($node_schema->getCollectionPath())); ?>"><?php __($node_schema->getDisplayName(0)); ?></a>
		<span class="divider">→</span>
		<a href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>"><?php __(_e($node->getTitle())); ?></a>
		<span class="divider">→</span>
		<a href="<?php __(_e($node->getEdgeCollectionPath($edge_schema))); ?>" title="<?php __(_a('edge_collection_title', $edge_schema->getDisplayName(), $node->getTitle())); ?>"><?php __($edge_schema->getDisplayName(0)); ?></a>
		<?php

		if (isset($edge)) {
			$end_node = $edge->getEndNode();

		?>
		<span class="divider">→</span>
		<a href="<?php __(_e($end_node->getPath())); ?>" title="<?php __(_e($end_node->getTitle())); ?>"><?php __(_e($end_node->getTitle())); ?></a>
		<?php

		}

		?>
	</nav>
	<div class="main-title-group">
		<h1 class="main-title"><?php __($title); ?></h1>
	</div>
	<nav class="crud">
		<ul>
			<li class="new"><a class="crud-button new" href="<?php __(_e($node->getNewEdgePath($edge_schema))); ?>" title="<?php __(_a('new_edge_button_title', $edge_schema->getDisplayName(), $node->getTitle())); ?>"><?php __(_m('new_edge_button_text')); ?></a></li>
			<?php

			if (isset($edge)) {

			?>
			<li class="edit"><a class="crud-button edit" href="<?php __(_e($edge->getEditPath())); ?>" title="<?php __(_a('edit_edge_button_title', $edge->getTitle())); ?>"><?php __(_m('edit_edge_button_text')); ?></a></li>
			<li class="view"><a class="crud-button view" href="<?php __(_e($edge->getPath())); ?>" title="<?php __(_a('view_edge_button_title')); ?>"><?php __(_m('view_edge_button_text')); ?></a></li>
			<?php

			}

			?>
			<li class="collection"><a class="crud-button collection" href="<?php __(_e($node->getEdgeCollectionPath($edge_schema))); ?>" title="<?php __(_a('view_edge_collection_button_title', $edge_schema->getDisplayName())); ?>"><?php __(_m('view_edge_collection_button_text')); ?></a></li>
			<li class="view-parent"><a class="crud-button view-parent" href="<?php __(_e($node->getPath())); ?>" title="<?php __(_a('edge_parent_button_title', $node->getTitle())); ?>"><?php __(_m('edge_parent_button_text', $node->getSchema()->getDisplayName())); ?></a></li>
		</ul>
	</nav>
</header>

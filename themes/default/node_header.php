<header class="main-header object node">
	<?php

	if (!isset($node_schema)) {
		$node_schema = $node->getSchema();
	}

	?>
	<nav class="breadcrumb" itemprop="breadcrumb">
		<a class="main-type" href="/<?php __(_e($node_schema->getSlug())); ?>/"><?php __($node_schema->getDisplayName(0)); ?></a>
		<?php

		if (isset($node)) {

		?>
		<span class="divider">→</span>
		<a href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>"><?php __(_e($node->getTitle())); ?></a>
		<?php

		}

		?>
	</nav>
	<div class="main-title-group">
		<h1 class="main-title"><?php __($title); ?></h1>
		<?php

		if (isset($abstract_property_name)) {
			$property = $node->getProperty($abstract_property_name);
			if ($property->hasValue()) {

		?>
		<div class="main-abstract">
		<?php

			require __path('types/' . $property->getSchema()->getType() . '.php');

		?>
		</div>
		<?php

			}
		}

		?>
	</div>
	<nav class="crud">
		<ul>
			<li class="new"><a class="crud-button new" href="<?php __(_e($node_schema->getNewPath())); ?>" title="<?php __(_a('new_node_button_title', $node_schema->getDisplayName())); ?>"><?php __(_m('new_node_button_text')) ?></a></li>
			<?php

			if (isset($node)) {

			?>
			<li class="edit"><a class="crud-button edit" href="<?php __(_e($node->getEditPath())); ?>" title="<?php __(_a('edit_node_button_title', $node->getTitle())); ?>"><?php __(_m('edit_node_button_text')); ?></a></li>
			<li class="view"><a class="crud-button view" href="<?php __(_e($node->getPath())); ?>" title="<?php __(_a('view_node_button_title', $node->getTitle())); ?>"><?php __(_m('view_node_button_text')); ?></a></li>
			<?php

			}

			?>
			<li class="collection"><a class="crud-button collection" href="<?php __(_e($node_schema->getCollectionPath())); ?>" title="<?php __(_a('view_node_collection_button_title', $node_schema->getDisplayName())); ?>"><?php __(_m('view_node_collection_button_text')); ?></a></li>
		</ul>
	</nav>
</header>

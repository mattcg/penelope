<header class="main-header object node">
	<?php

	if (!isset($node_schema)) {
		$node_schema = $node->getSchema();
	}

	?>
	<nav class="breadcrumb" itemprop="breadcrumb">
		<a class="main-type" href="<?php __(_e($node_schema->getCollectionPath())); ?>"><?php __($node_schema->getDisplayName(0)); ?></a>
		<?php

		if (isset($node)) {

		?>
		<span class="divider">→</span>
		<a href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>"><?php __(_e($node->getTitle())); ?></a>
		<?php

		}

		?>
	</nav>
	<?php

	if (isset($node) and $option = $node_schema->getOption('format.figure') and $node->getProperty($option)->hasValue()) {
		$has_figure = true;
	} else {
		$has_figure = false;
	}

	?>
	<div class="main-title-group<?php

	if ($has_figure) {
		__(' contains-figure');
	}

	?>">
		<a name="main"><h1 class="main-title"><?php __($title); ?></h1></a>
		<?php

		if (isset($node)) {
			$option = $node_schema->getOption('format.abstract');
			if ($option and $property = $node->getProperty($option) and $property->hasValue()) {

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
		<?php

		if ($has_figure) {
			$property = $node->getProperty($node_schema->getOption('format.figure'));

		?>
		<div class="main-figure">
		<?php

			require __path('types/' . $property->getSchema()->getType() . '.php');

		?>
		</div>
		<?php

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
			<li class="collection"><a class="crud-button collection" href="<?php __(_e($node_schema->getCollectionPath())); ?>" title="<?php __(_a('view_node_collection_button_title', $node_schema->getDisplayName(0))); ?>"><?php __(_m('view_node_collection_button_text')); ?></a></li>
		</ul>
	</nav>
	<?php

	if (isset($node)) {

	?>
	<nav class="sections">
		<ol>
			<li><a href="#main"><?php __(_m('node_main_title')); ?></a></li>
			<li><a href="#edges"><?php __(_m('node_edges_title')); ?></a></li>
		</ol>
	</nav>
	<?php

	}

	?>
</header>

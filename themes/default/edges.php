<main class="collection edges">
	<?php

	require __path('edge_header.php');

	?>
	<div class="main-body">
		<?php

		if (empty($edges)) {

		?>
		<p><?php __(_m('edge_collection_empty', $edge_schema->getDisplayName())); ?></p>
		<?php

		} else {

		?>
		<nav class="collection edges">
			<ul>
				<?php

				foreach ($edges as $edge) {
					$related_node = $edge->getEndNode();

				?>
				<li class="object <?php __class('object-type-' . $related_node->getSchema()->getName()); ?>">
					<a class="object-link" href="<?php __(_e($related_node->getPath())); ?>" title="<?php __(_s($related_node->getTitle())); ?>">
						<h1 class="object-title"><?php __(_e($related_node->getTitle())); ?></h1>
						<h2 class="object-subtitle"><?php __(_e($related_node->getSchema()->getDisplayName())); ?></h2>
					</a>
					<a class="edge-link" href="<?php __(_e($edge->getPath())); ?>" title="<?php __(_s($edge->getTitle())); ?>"><?php __(_m('node_edge_link_title')); ?></a>
				</li>
				<?php

				}

				?>
			</ul>
		</nav>
		<?php

		}

		?>
	</div>
</main>

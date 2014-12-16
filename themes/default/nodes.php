<main class="collection nodes">
	<?php

	require __path('node_header.php');

	?>
	<div class="main-body">
		<?php

		if (empty($nodes)) {

		?>
		<p><?php __(_m('node_collection_empty', $node_schema->getDisplayName())); ?></p>
		<?php

		} else {

			if ($prev_page) {
				$query_string = http_build_query(array('p' => $prev_page));
				if (!empty($properties)) {
					$query_string .= http_build_query($properties);
				}

		?>
		<p><a class="crud-button paging prev" href="?<?php __($query_string); ?>"><?php __(_m('collection_prev_page')); ?></a></p>
		<?php

			}

		?>
		<nav class="collection nodes">
			<ul>
				<?php

				foreach ($nodes as $node) {

				?>
				<li class="object <?php __class('object-type-' . $node->getSchema()->getName()); ?>">
					<a class="object-link" href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>">
						<h1 class="object-title"><?php __(_e($node->getTitle())); ?></h1>
						<h2 class="object-subtitle"><?php __(_e($node->getSchema()->getDisplayName())); ?></h2>
					</a>
				</li>
				<?php

				}

				?>
			</ul>
		</nav>
		<?php

			if ($next_page) {
				$query_string = http_build_query(array('p' => $next_page));
				if (!empty($properties)) {
					$query_string .= http_build_query($properties);
				}

		?>
		<p><a class="crud-button paging next" href="?<?php __($query_string); ?>"><?php __(_m('collection_next_page')); ?></a></p>
		<?php

			}

		}

		?>
	</div>
</main>

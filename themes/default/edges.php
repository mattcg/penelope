<main class="collection edges">
	<?php

	require __path('edge_header.php');

	?>
	<div class="body">
		<?php

		if (empty($edges)) {

		?>
		<p><?php __(_m('edge_collection_empty', $edge_schema->getName())); ?></p>
		<?php

		} else {

		?>
		<nav class="collection edges">
			<ul>
				<?php

				foreach ($edges as $edge) {

				?>
				<li class="object"><a href="<?php __($edge->getPath()); ?>" title="<?php __($edge->getTitle()); ?>"><?php __($edge->getTitle()); ?></a></li>
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

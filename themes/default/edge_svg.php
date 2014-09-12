<?php __('<?xml version="1.0"?>'); ?>
<svg viewBox="0 0 400 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<style type="text/css">
		<![CDATA[

		.node {
			fill: white;
		}

		.node,
		.edge {
			stroke: #999;
			stroke-width: 5;
		}

		.label {
			font-family: "Helvetica Neue", Helevetica, Arial, sans-serif;
			font-size: 14px;
			fill: #999;
		}

		#triangle {
			fill: #999;
		}

		]]>
	</style>
	<marker id="triangle"
		viewBox="0 0 10 10" refX="0" refY="5"
		markerUnits="strokeWidth"
		markerWidth="4" markerHeight="3"
		orient="auto">
	<path d="M 0 0 L 10 5 L 0 10 z" />
	</marker>
	<a xlink:href="<?php __(_e($edge->getStartNode()->getPath())); ?>" target="_top">
		<circle cx="50" cy="50" r="45" class="node node-out" />
		<text class="label node-label" x="50" y="52.5" text-anchor="middle"><?php __(_e(mb_strimwidth($edge->getStartNode()->getTitle(), 0, 10, '…'))); ?></text>
	</a>
	<line x1="120" y1="50" x2="265" y2="50" marker-end="url(#triangle)" class="edge" />
	<text class="label edge-label" x="200" y="35" text-anchor="middle"><?php __(_e($edge->getSchema()->getDisplayName())); ?></text>
	<a xlink:href="<?php __(_e($edge->getEndNode()->getPath())); ?>" target="_top">
		<circle cx="350" cy="50" r="45" class="node node-in" />
		<text class="label node-label" x="350" y="52.5" text-anchor="middle"><?php __(_e(mb_strimwidth($edge->getEndNode()->getTitle(), 0, 10, '…'))); ?></text>
	</a>
</svg>

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    Penelope
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @since      File available since Release 1.0.0
 */

/*jshint node:true, browser:true*/

'use strict';

var delegate = require('dom-delegate')(document.body);

// Automatically add new blank fields to edit forms on focus.
delegate.on('focus', 'form.object .new', function(event, target) {
	var name, siblings, value, i, l, empty, clone, cloneParent;

	name = target.name;
	siblings = target.form.querySelectorAll(target.tagName + '[name="' + name + '"]');

	// Don't insert a new node if two or more are empty.
	empty = 0;
	for (i = 0, l = siblings.length; i < l; i++) {
		value = siblings[i].value;

		if (!value || !String(value).trim()) {
			empty++;
		}

		if (empty > 1) {
			return;
		}
	}

	clone = target.cloneNode(true);
	clone.value = target.defaultValue;

	target.classList.remove('new');
	target.removeAttribute('id');

	if (target.parentNode.tagName.toLowerCase() === 'li') {
		cloneParent = target.parentNode.cloneNode(false);
		cloneParent.appendChild(clone);
		target.parentNode.parentNode.insertBefore(cloneParent, target.parentNode.nextSibling);
	} else {
		target.parentNode.insertBefore(clone, siblings[siblings.length - 1].nextSibling);
	}
});

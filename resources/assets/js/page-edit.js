$(document).ready(function() {
	var parentField = $('#attributes_parent'),
		parentVal = parentField.val(),
		siblingField = $('#attributes_siblings')
	;

	parentField.change(function () {
		if (parentField.val() !== parentVal) {
			siblingField.attr('disabled', 'disabled');
		} else {
			siblingField.removeAttr('disabled');
		}
	})
});
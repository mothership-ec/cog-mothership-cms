/**
 * Activate AJAX request when filter form values are changed
 *
 * @param ajaxUrl               URL to send AJAX request to
 * @param filterDestinationID   Identifier to inject response HTML into
 * @param method                Method to submit the form with - recommended to use GET for pagination
 * @param formID                The filtering form ID, defaults to '#page-filter-form'
 * @param paginationMenuID      The ID of the pagination menu, defaults to '#pagination-menu'
 */
function filterPages(ajaxUrl, filterDestinationID, method, formID, paginationMenuID) {

	var	filterDestination = $(filterDestinationID),
		form = $(formID || '#page-filter-form'),
		submitMethod = method || 'get',
		button = form.find(':submit'),
		paginationMenuLinks = paginationMenuID ? paginationMenuID + ' a' : '#pagination-menu a'
		;

	// Validate form method
	if (submitMethod.toLowerCase() !== 'get' && submitMethod.toLowerCase !== 'post') {
		alert('Third parameter must be either \'post\' or \'get\'');
	}

	// Declare functions
	/**
	 * Collect form data and append it to the hrefs of the pagination links if the method is set to get
	 */
	function appendFormDataToPagination() {
		if (submitMethod.toLowerCase() === 'get') {
			$(paginationMenuLinks).attr('href', function (i) {
				var url = $(this).attr('href');
				return appendFormDataToUrl(url, false);
			});
		}
	}

	/**
	 * Collection form data and append it to the URL in the address bar
	 */
	function appendFormDataToAddressUrl() {
		if (submitMethod.toLowerCase() === 'get') {
			var url = window.location.href;
			window.history.pushState('string', 'Title', appendFormDataToUrl(url, true));
		}
	}

	/**
	 * Collection form data and add it to the end of a URL
	 *
	 * @param url {string}
	 * @param removeExisting {boolean}
	 * @returns {string}
	 */
	function appendFormDataToUrl(url, removeExisting) {
		var baseUrl = url.split('?')[0],
			dataString = form.serialize()
			;

		if (removeExisting && dataString) {
			return baseUrl + '?' + dataString;
		} else if (removeExisting) {
			return baseUrl;
		}

		return baseUrl + '?' + replaceParams(url);
	}

	/**
	 * Replace the parameters of the URL with the data from the form
	 *
	 * @param url {string}
	 * @returns {string}
	 */
	function replaceParams(url) {
		var paramString = url.substring(url.indexOf('?') + 1),
			paramArray = paramString.split('&'),
			dataArray = form.serializeArray(),
			params = {},
			newParamString = '',
			first = true,
			inputs = $(form).find('[name]'),
			noValueInputs = []
		;

		$.each(inputs, function (i, v) {
			noValueInputs.push($(v).attr('name'));
		});

		$.each(dataArray, function (i, v) {
			var index = noValueInputs.indexOf(v['name']);

			if (index > -1) {
				noValueInputs.splice(index, 1);
			}
		});

		// Split parameters into key/value pairs
		$.each(paramArray, function(i, v) {
			var split = v.split('=')
				;

			if (split.length === 2) {
				var name = decodeURIComponent(split[0]);

				if (noValueInputs.indexOf(name) === -1) {

					params[name] = split[1];
				}
			}
		});

		// Build params object from array of parameters taken from the URL
		$.each(dataArray, function(i, v) {
			// Check whether it is a multivalue field e.g. checkboxes
			var pattern = /\[\]$/,
				value = v['value'].split(' ');

			// URL encode values, replacing spaces with pluses
			$.each(value, function (key, word) {
				value[key] = encodeURIComponent(word);
			});

			v['value'] = value.join('+');

			// If form value is an array, rebuild array as the value for that property on the params object
			if (pattern.test(v['name'])) {
				if (typeof params[v['name']] === 'undefined' || params[v['name']].constructor !== Array) {
					params[v['name']] = [];
				}
				params[v['name']].push(v['value']);
			} else {
				params[v['name']] = v['value'];
			}
		});

		// Loop through params to build new parameter string
		$.each(params, function(i, v) {
			if (v.constructor === Array) {
				$.each(v, function (index, value) {
					if (false === first) {
						newParamString += '&';
					}
					newParamString += encodeURIComponent(i) + '=' + value;
					first = false;
				})
			} else {
				if (false === first) {
					newParamString += '&';
				}
				newParamString += encodeURIComponent(i) + '=' + v;
				first = false;
			}
		});

		return newParamString;
	}

	// Hide submit button
	button.hide();

	appendFormDataToPagination();

	// Submit form when values change and populate the filter destination with the result
	form.change(function () {
		filterDestination.addClass('loading');
		$.ajax({
			url: ajaxUrl,
			type: submitMethod,
			data: form.serialize(),
			success: function (data) {
				filterDestination.html(data);
				appendFormDataToPagination();
				appendFormDataToAddressUrl();
			}
		});
		filterDestination.removeClass('loading');

		appendFormDataToPagination();
		appendFormDataToAddressUrl();
	});
}
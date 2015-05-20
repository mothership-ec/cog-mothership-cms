/**
 * Activate AJAX request when filter form values are changed
 *
 * @param ajaxUrl               URL to send AJAX request to
 * @param filterDestinationID   Identifier to inject response HTML into
 * @param method                Method to submit the form with
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

	if (submitMethod.toLowerCase() !== 'get' && submitMethod.toLowerCase !== 'post') {
		alert('Third parameter must be either \'post\' or \'get\'');
	}

	function appendFormDataToPagination() {
		if (submitMethod.toLowerCase() === 'get') {
			$(paginationMenuLinks).attr('href', function (i) {
				var url = $(this).attr('href');
				return appendFormDataToUrl(url);
			});
		}
	}

	function appendFormDataToAddressUrl() {
		var url = window.location.href;
		window.history.pushState('string', 'Title', appendFormDataToUrl(url));
	}
	
	function appendFormDataToUrl(url) {
		var	dataString = form.serialize(),
			baseUrl = url.split('?')[0]
			;

		if (dataString) {
			return baseUrl + '?' + replaceParams(url);
		}

		return url;
	}

	function replaceParams(url) {
		var paramString = url.substring(url.indexOf('?') + 1),
			paramArray = paramString.split('&'),
			dataArray = form.serializeArray(),
			params = {},
			newParamString = '',
			first = true
		;

		$.each(paramArray, function(i, v) {
			var split = v.split('=');

			if (split.length === 2) {
				params[decodeURIComponent(split[0])] = split[1];
			}
		});

		$.each(dataArray, function(i, v) {
			var pattern = /\[\]$/;

			if (pattern.test(v['name'])) {
				if (typeof params[v['name']] === 'undefined' || params[v['name']].constructor !== Array) {
					params[v['name']] = [];
				}
				params[v['name']].push(v['value']);
			} else {
				params[v['name']] = v['value'];
			}
		});

		console.log($.param(params));

		$.each(params, function(i, v) {
			if (false === first) {
				newParamString += '&';
			}
			newParamString += encodeURIComponent(i) + '=' + encodeURIComponent(v);
			first = false;
		});

		return newParamString;
	}

	button.hide();

	appendFormDataToPagination();

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
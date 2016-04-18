$(document).ready(function() {
	var cookieWarning = $('#ms-cookie-warning'), // Container for cookie warning
		cookieLink = $('#ms-cookie-link'), // Link that will make the cookie warning disappear
		cookieName = cookieLink.attr('data-cookie-name'), // Name of the cookie that confirms that the user understands
		cookie = getCookie(cookieName) // The value of the cookie that confirms that the user understands
	;

	if (cookie) {
		// If the user has already confirmed that they understand, hide the warning
		cookieWarning.hide();
	} else {
		// When the user clicks the link, assign a cookie and hide the warning
		cookieLink.on('click', function (e) {
			e.preventDefault();
			document.cookie = encodeURIComponent(cookieName) + '=1; path=/';
			cookieWarning.hide();
		});
	}

});

// Taken from http://stackoverflow.com/questions/10730362/get-cookie-by-name
function getCookie(name) {
	if (name) {
		var cookies = '; ' + document.cookie,
			parts = cookies.split('; ' + name + '=')
			;

		if (parts.length == 2) {
			return parts.pop().split(';').shift();
		}
	}

	return false;
}
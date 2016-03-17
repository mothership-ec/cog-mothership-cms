$(document).ready(function() {
	var cookieWarning = $('#ms-cookie-warning'),
		cookieLink = $('#ms-cookie-link'),
		cookieName = cookieLink.attr('data-cookie-name'),
		cookie = getCookie(cookieName)
	;
console.log(document.cookie);
	if (cookie) {
		cookieWarning.hide();
	} else {
		cookieLink.on('click', function (e) {
			e.preventDefault();
			document.cookie = encodeURIComponent(cookieName) + '=1; path=/';
			console.log(document.cookie);
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
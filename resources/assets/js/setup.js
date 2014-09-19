$(function() {
	// CMS table JS setup
	$('.table-filter.page').dataTable({
		iDisplayLength: 25,
		"oLanguage": {
			"sLengthMenu": 'Display <select>'+
			'<option value="25">25</option>'+
			'<option value="50">50</option>'+
			'<option value="100">100</option>'+
			'<option value="200">200</option>'+
			'<option value="-1">All</option>'+
			'</select> pages',
		"sInfo": "Showing (_START_ to _END_) of _TOTAL_ Pages"}
    }).columnFilter({
		aoColumns: [
			{ type: "text" },
			{ type: "text" },
			{ type: "text" },
			{ type: "text" },
			{ type: "text" },
			null,
			null
		]
	});
});
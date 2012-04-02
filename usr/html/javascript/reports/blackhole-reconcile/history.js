/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	var data;

	$('.progress, .message').hide();

	$('#tableData').html(data);
	$(".tablesorter").tablesorter({
		sortList: [[0, 1]],
		widgets: ['zebra']
	})
	.tablesorterPager({container: $('#pager')});
});

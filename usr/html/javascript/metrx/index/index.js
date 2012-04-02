/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	var searchTimeout;

	searchTimeout = undefined;

	$('#tabs').tabs();
	$('.metrxRow .links').hide();
	$('.metrxRow').live('click', function () {
		var urlLink;

		urlLink = $(this).find('input[name="urlLink"]').val();
		window.location = urlLink;
	});
	$('.metrxRow').live('mouseover', function () {
		$(this).find('.links').show();
	});
	$('.metrxRow').live('mouseout', function () {
		$(this).find('.links').hide();
	});
});

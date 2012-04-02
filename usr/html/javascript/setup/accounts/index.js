/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function createAccounts() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/setup/accounts/create';
	params = {};

	$('.progress').show();

	$.post(
		url,
		params,
		function (data) {
			$('.progress').hide();

			$('#accounts').html(data);
			$("#acct-table").tablesorter({
				sortList: [[0, 0]],
				widgets: ['zebra']
			});
			$('#accounts, #next-step').show();
		},
		'html'
	);
}

$(document).ready(function () {
	$('.message, .progress, #next-step').hide();

	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});

	createAccounts();
});

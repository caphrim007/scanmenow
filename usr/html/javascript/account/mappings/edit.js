/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function createMapping() {
	var baseUrl, url, params, mapName, accountId;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/account/mappings/create';
	params = $('#form-submit').serialize();
	mapName = $('#form-submit input[name=map-name]');
	accountId = $('#form-submit input[name=accountId]').val();

	if (mapName.val() === '') {
		return;
	}

	$('.progress').show();
	$('#btn-save').attr('disabled', true);

	$.post(
		url,
		params,
		function (data) {
			if (data.status === true) {
				window.location = baseUrl + '/account/mappings?accountId=' + accountId;
			} else {
				$('#btn-save').attr('disabled', false);
			}
		},
		'json'
	);
}

$(document).ready(function () {
	$('#btn-save').click(function () {
		createMapping();
	});

	$('#form-submit input[name=map-name]').keypress(function (event) {
		if (event.which === 13) {
			event.preventDefault();
			createMapping();
			return true;
		}
	});
});

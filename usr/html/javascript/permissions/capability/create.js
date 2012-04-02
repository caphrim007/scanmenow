/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	var baseUrl;

	baseUrl = $('#form-edit input[name=base-url]').val();

	$('.message, .progress').hide();

	$('#btn-save').click(function () {
		var url, params;

		url = baseUrl + '/permissions/capability/save';
		params = $('#form-submit').serialize();

		$('.progress').show();
		$('#btn-save').attr('disabled', true);

		$.post(
			url,
			params,
			function (data) {
				$('.progress').hide();

				if (data.status === true) {
					window.location = baseUrl + '/permissions/capability';
				} else {
					$('#btn-save').attr('disabled', false);
					$('.error').show();
				}
			},
			'json'
		);
	});
});

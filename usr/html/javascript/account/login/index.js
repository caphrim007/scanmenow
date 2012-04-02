/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function authWithCert() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/opt/auth/cert/account/login/certificate-login';
	params = {};

	$.post(
		url,
		params,
		function (data) {
			var block;

			if (data === null) {
				$('#certAuthBlockStatus').hide();
				block = $('#certAuthBlock');
				block.find('.message .content').html("Encountered an unknown error while doing certificate authentication");
				block.find('.message').show();

				$('#form-submit :input').attr('disabled', '');
				$('#form-submit input[name=username]').focus();
			} else if (data.status === true) {
				window.location = baseUrl;
			} else {
				$('#certAuthBlockStatus').hide();
				block = $('#certAuthBlock');
				block.find('.message .content').html(data.message);
				block.find('.message').show();

				$('#form-submit :input').attr('disabled', '');
				$('#form-submit input[name=username]').focus();
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var baseUrl;

	baseUrl = $('#form-edit input[name=base-url]').val();

	$('.message').hide();
	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});

	$('#btn-save').click(function () {
		var url, params;

		url = baseUrl + '/account/login/standard-login';
		params = $('#form-submit').serialize();

		$('#form-submit :input').attr('disabled', 'disabled');

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					window.location = baseUrl;
				} else {
					$('#form-submit input[name=password]').val('');
					$('#form-submit .message .content').html(data.message);
					$('#form-submit .message').show();
					$('#form-submit :input').attr('disabled', false);
				}
			},
			'json'
		);
	});

	$('#form-submit input[name=password]').keypress(function (event) { 
		if (event.which === 13) {
			event.preventDefault();
			$('#btn-save').trigger('click');
			return true;
		}
	});

	if ($('#form-edit input[name=certAuth]').val() === true) {
		$('#form-submit :input').attr('disabled', 'disabled');
		authWithCert();
	} else {
		$('#form-submit input[name=username]').focus();
		$('#certAuthBlock').hide();
	}
});

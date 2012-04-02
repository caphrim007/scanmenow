/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	var baseUrl;

	baseUrl = $('#form-edit input[name=base-url]').val();

	$('.message, .progress').hide();

	$('#form-submit input[type=password]').keypress(function (event) { 
		if (event.which === 13) {
			event.preventDefault();
			$('#btn-save').trigger('click');
			return true;
		}
	});

	$('#btn-save').click(function () {
		var url, params, accountId, newPassword, repeatPassword;

		url = baseUrl + '/account/password/save';
		params = $('#form-submit').serialize();
		accountId = $('#form-submit input[name=accountId]').val();

		newPassword = $('#form-submit input[name=newPassword]').val();
		repeatPassword = $('#form-submit input[name=repeatPassword]').val();

		if (newPassword === '' || repeatPassword === '') {
			$('.message').html('Passwords cannot be empty');
			$('.message').show();
			return;
		}

		$('.progress').show();
		$('#btn-save').attr('disabled', true);

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					window.location = baseUrl + '/account/modify/edit?id=' + accountId;
				} else {
					$('.progress').hide();
					$('#btn-save').attr('disabled', false);

					$('.message').html(data.message);
					$('.message').show();
				}
			},
			'json'
		);
	});

	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});
});

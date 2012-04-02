/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	var baseUrl;

	baseUrl = $('#form-edit input[name=base-url]').val();

	$('.message').hide();
	$('#tabs').tabs();

	$('input[type=text]').keypress(function (event) { 
		if (event.which === 13) {
			event.preventDefault();
			$('#btn-save').trigger('click');
			return true;
		}
	});

	$('#btn-save').click(function () {
		var url, params;

		url = baseUrl + '/config/modify/save';
		params = $('.form-submit').serialize();

		$(this).attr('disabled', true);

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					window.location = baseUrl + '/admin/';
				} else {
					$('#btn-save').attr('disabled', false);
					$('.message .content').html(data.message);
					$('.message').show();
				}
			},
			'json'
		);
	});

	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});


	$('#url input[name=exp_should]').click(function () {
		if ($('#url input[name=exp_should]:checked').val() === 'expire') {
			$('#url .expirationShouldActions').show();
		} else {
			$('#url .expirationShouldActions').hide();
		}
	});

	$('#url input[name=exp_action]').click(function () {
		if ($('#url input[name=exp_action]:checked').val() === 'cmd') {
			$('#url .expirationActions').show();
		} else {
			$('#url .expirationActions').hide();
		}
	});

	$('#url input[name=exp_action]:checked').trigger('click');
	$('#url input[name=exp_should]:checked').trigger('click');
});

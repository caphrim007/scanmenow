/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	var baseUrl, limits;

	baseUrl = $('#form-edit input[name=base-url]').val();

	$('.message, .progress').hide();

	$('#btn-save').click(function () {
		var url, params, accountId;

		url = baseUrl + '/account/interface/save';
		params = $('#form-submit').serialize();
		accountId = $('#form-submit input[name=accountId]').val();

		$('.progress').show();
		$('#btn-save').attr('disabled', true);

		$.post(
			url,
			params,
			function (data) {
				$('.progress').show();
				if (data.status === true) {
					window.location = baseUrl + '/account/modify/edit?id=' + accountId;
				} else {
					$('.message .content').html(data.message);
					$('.message').show();
					$('#btn-save').attr('disabled', false);
				}
			},
			'json'
		);
	});

	$(".limit").slider({
		range: "min",
		value: 15,
		min: 1,
		max: 30,
		slide: function (event, ui) {
			var row;
			row = $(ui.handle).parents('.limitRow');
			row.find('.limitVal').val(ui.value);
			row.find('.limitDisp').html(ui.value);
		}
	});
	limits = $('.limit');
	$.each(limits, function (index, value) {
		var slider, currentVal;

		slider = $(limits[index]);
		currentVal = slider.parents('.limitRow').find('.limitVal').val();
		slider.slider('value', currentVal);
	});
});

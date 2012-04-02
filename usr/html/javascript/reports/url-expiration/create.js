/*jslint browser: true, devel: true, cap: false, maxerr: 65535, forin: true, nomen: false*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

var selectedUrls;

selectedUrls = [];

function loadTable(time, direction) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/url-expiration/table';
	params = {
		'date': time,
		'direction': direction,
		'page': $('#form-edit input[name=page]').val()
	};

	$('.progress').show();

	$.get(
		url,
		params,
		function (data) {
			$('.progress').hide();

			$('#content').html(data.message);
			$('#content').show();
			$('#postSearchOps').show();

			if (data.totalPages <= 1) {
				$('#pager').hide();
			} else {
				$("#pager").pager({
					pagenumber: data.currentPage,
					pagecount: data.totalPages,
					buttonClickCallback: function (pageClickedNumber) {
						var theDate;

						$('#form-edit input[name=page]').val(pageClickedNumber);
						if (direction === 'before') {
							theDate = $('#before').datepicker('getDate');
							loadTable(theDate.getTime(), 'before');
						} else {
							theDate = $('#after').datepicker('getDate');
							loadTable(theDate.getTime(), 'after');
						}
					}
				}).show();
			}
		},
		'json'
	);
}

function loadInfiniteTable() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/url-expiration/table';
	params = {
		'date': 'infinite',
		'direction': 'infinite',
		'page': $('#form-edit input[name=page]').val()
	};

	$('.progress').show();

	$.get(
		url,
		params,
		function (data) {
			$('.progress').hide();

			$('#content').html(data.message);
			$('#content').show();
			$('#postSearchOps').show();

			if (data.totalPages <= 1) {
				$('#pager').hide();
			} else {
				$("#pager").pager({
					pagenumber: data.currentPage,
					pagecount: data.totalPages,
					buttonClickCallback: function (pageClickedNumber) {
						$('#form-edit input[name=page]').val(pageClickedNumber);
						loadInfiniteTable();
					}
				}).show();
			}
		},
		'json'
	);
}

function in_array(needle, haystack) {
	var idx;

	for (idx in haystack) {
		if (haystack[idx] === needle) {
			return true;
		}
	}

	return false;
}

function array_remove(needle, haystack) {
	var result, idx;

	result = [];

	for (idx in haystack) {
		if (haystack[idx] === needle) {
			continue;
		} else {
			result.push(haystack[idx]);
		}
	}

	return result;
}

function changeExpiration() {
	var baseUrl, url, idx, param, params, dateScheduled;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/url-expiration/report';
	params = [];

	dateScheduled = Date.parse($('#startOnDate').val() + ' ' + $('#startOnTime').val());
	param = {
		'name': 'newExpiration',
		'value': dateScheduled.toString('yyyy-MM-ddTHH:mm:ss')
	};
	params.push(param);

	for (idx in selectedUrls) {
		param = {
			'name': 'urls[]',
			'value': selectedUrls[idx]
		};
		params.push(param);
	}

	$('.progress').show();
	$('#btn-save').attr('disabled', 'disabled');

	$.post(
		url,
		params,
		function (data) {
			$('.progress').hide();

			if (data.status === true) {
				window.location = baseUrl + '/reports/url-expiration';
			} else {
				$('#btn-save').attr('disabled', '');
				// TODO: Display error message
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var dateScheduled, dateBefore, dateAfter;

	$('.progress, .message, #postSearchOps').hide();

	dateScheduled = new Date();
	$('#startOnDate').dateEntry({
		spinnerImage: 'usr/images/spinnerUpDown.png',
		spinnerSize: [15, 16, 0],
		spinnerIncDecOnly: true,
		defaultDate: dateScheduled
	});

	$('#startOnTime').timeEntry({
		spinnerImage: 'usr/images/spinnerUpDown.png',
		spinnerSize: [15, 16, 0],
		spinnerIncDecOnly: true,
		defaultDate: dateScheduled
	});

	$('#startOnDate').dateEntry('setDate', null);
	$('#startOnTime').timeEntry('setTime', null);

	$('.urlExpireCol').click(function () {
		var theDate;

		$('.urlExpireCol').removeClass('selectedExpireCol');

		$(this).addClass('selectedExpireCol');
		if ($(this).hasClass('before')) {
			theDate = $('#before').datepicker('getDate');
			loadTable(theDate.getTime(), 'before');
		} else if ($(this).hasClass('after')) {
			theDate = $('#after').datepicker('getDate');
			loadTable(theDate.getTime(), 'after');
		} else {
			loadInfiniteTable();
		}
	});

	dateBefore = $("#before").datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		changeYear: true,
		onSelect: function (selectedDate) {
			var instance, theDate, timestamp;

			instance = $(this).data("datepicker");
			theDate = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
			timestamp = theDate.getTime();

			loadTable(timestamp, 'before');
		}
	});

	dateAfter = $("#after").datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		changeYear: true,
		onSelect: function (selectedDate) {
			var instance, theDate, timestamp;

			instance = $(this).data("datepicker");
			theDate = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
			timestamp = theDate.getTime();

			loadTable(timestamp, 'after');
		}
	});

	$('.url-list .row input[type=checkbox]').live('click', function () {
		var urlId, toDo;

		urlId = $(this).val();
		toDo = $(this).attr('checked');

		if (toDo === true) {
			if (!in_array(urlId, selectedUrls)) {
				selectedUrls.push(urlId);
			}
		} else {
			if (in_array(urlId, selectedUrls)) {
				selectedUrls = array_remove(urlId, selectedUrls);
			}
		}
	});

	$('#btn-save').click(function () {
		changeExpiration();
	});
});

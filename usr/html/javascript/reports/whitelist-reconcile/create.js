/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function create() {
	var baseUrl, url, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/whitelist-reconcile/report';
	params = $('#form-submit').serialize();

	isSearching = $('#form-edit').data('isSearching');
	if (isSearching !== false) {
		return false;
	} else {
		$('#form-edit').data('isSearching', true);
		$('.progress').show();
	}

	$.post(
		url,
		params,
		function (data) {
			$('.progress').hide();
			$('#form-edit').data('isSearching', false);

			if (data.status === true) {
				window.location = baseUrl + '/reports/whitelist-reconcile';
			} else {
				$('.message .content').html(data.message);
				$('.message').show();
			}
		},
		'json'
	);

	return true;
}

function refreshTable() {
	var baseUrl, url, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/whitelist-reconcile/refresh-table';
	params = {};

	isSearching = $('#form-edit').data('isSearching');
	if (isSearching !== false) {
		return false;
	} else {
		$('#form-edit').data('isSearching', true);
		$('.progress').show();
	}

	$.get(
		url,
		params,
		function (data) {
			$('.progress').hide();
			$('#form-edit').data('isSearching', false);

			$('#whitelistBlock').html(data);
			$(".tablesorter").tablesorter({
				sortList: [[1, 1]],
				widgets: ['zebra'],
				headers: {
					0: {
						sorter: false
					},
					3: {
						sorter: false
					}
				}
			});

			$('#form-ops').show();
		},
		'html'
	);

	return true;
}

$(document).ready(function () {
	var searchTimeout, autocompleteUrl, url, baseUrl;

	searchTimeout = undefined;

	baseUrl = $('#form-edit input[name=base-url]').val();
	$('.progress, .message, #form-ops').hide();

	$('.progress, .message, #mesg-already-exists').hide();
	$('#form-edit').data('isSearching', false);
	$('#form-edit').data('isSaving', false);

	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});

	$('#selectToggle').live('click', function () {
		var checkbox;

		checkbox = $('#whitelistTable .row').find(':checkbox');
		checkbox.attr('checked', !checkbox.is(':checked'));
	});

	$('#form-ops input[type=button]').click(function () {
		create();
	});

	refreshTable();
});

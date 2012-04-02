/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function create() {
	var baseUrl, url, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/blackhole-reconcile/report';
	params = $('#form-submit').serializeArray();

	isSearching = $('#form-edit').data('isSearching');
	if (isSearching !== false) {
		return false;
	} else {
		$('#form-edit').data('isSearching', true);
		$('.progress').show();
	}

	$('#btn-save').attr('disabled', true);

	$.post(
		url,
		params,
		function (data) {
			$('.progress').hide();
			$('#form-edit').data('isSearching', false);

			if (data.status === true) {
				window.location = baseUrl + '/reports/blackhole-reconcile';
			} else {
				$('#btn-save').attr('disabled', false);
				$('#blackholeBlock, #form-ops').hide();
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
	url = baseUrl + '/reports/blackhole-reconcile/refresh-table';
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

			$('#blackholeBlock').html(data);
			$('#blackholeTable .undoFixBrokenMesg').hide();
			$('#blackholeTable .undoFixBroken').hide();

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

		checkbox = $('#blackholeTable .row').find(':checkbox');
		checkbox.attr('checked', !checkbox.is(':checked'));
	});

	$('#form-ops input[type=button]').click(function () {
		create();
	});

	$('#blackholeTable .row img.warning').live('click', function () {
		var row;

		row = $(this).parents('.row');
		row.find('input[type=hidden]').attr('disabled', false);
		row.find('td').addClass('highlighted');
		row.find('img.warning').hide();
		row.find('.fixBroken').hide();
		row.find('.undoFixBroken, .undoFixBrokenMesg').fadeTo(1000, 1);
	});

	$('#blackholeTable .row .undoFixBrokenLink').live('click', function () {
		var row;

		row = $(this).parents('.row');
		row.find('input[type=hidden]').attr('disabled', true);
		row.find('td').removeClass('highlighted');
		row.find('.undoFixBroken, .undoFixBrokenMesg').hide();
		row.find('img.warning, .fixBroken').fadeTo(1000, 1);
	});

	$('#with-selected .selected-recreate').live('click', function () {
		var checkbox;

		checkbox = $('#blackholeTable .row').find(':checkbox:checked');

		// Sets the entries up to be fixed
		checkbox.parents('.row').find('img.warning').trigger('click');

		// Undoes the check mark
		checkbox.attr('checked', false);

		// Just in case the master checkbox toggle is also checked, uncheck it
		$('#selectToggle').attr('checked', false);
	});

	refreshTable();
});

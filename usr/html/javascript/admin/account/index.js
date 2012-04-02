/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForAccounts() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/account/search';
	params = $('#form-edit, #form-accounts').serialize();

	$('.progress').show();

	$.get(
		url,
		params,
		function (data) {
			$('.progress').hide();

			$('#content').html(data.message);
			$('#content .icons img').hide();
			$('#content').show();

			if (data.totalPages <= 1) {
				$('#pager').hide();
			} else {
				$("#pager").pager({
					pagenumber: data.currentPage,
					pagecount: data.totalPages,
					buttonClickCallback: function (pageClickedNumber) {
						$('#form-edit input[name=page]').val(pageClickedNumber);
						searchForAccounts();
					}
				}).show();
			}
		},
		'json'
	);
}

function deleteAccount(accountId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/account/delete';
	params = {
		'id': accountId
	};

	$.post(
		url,
		params,
		function (data) {
			if (data.status === true) {
				searchForAccounts();
			} else {
				return;
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = undefined;
	params = {};

	$('#searchMenu').hide();

	$('.select-all').click(function () {
		var block;

		block = $(this).parents('.block');
		block.find('input[type=checkbox]').attr('checked', true);
		block.find('.select-every').show();
	});
	$('.select-none').click(function () {
		var block;

		block = $(this).parents('.block');
		block.find('input[type=checkbox]').attr('checked', false);
	});

	$('.icons img.trash').live('click', function (ev) {
		var accountId, resp;

		accountId = $(this).parents('.row').find('input[name=accountId]').val();
		resp = confirm('Are you sure you want to delete this account? Everything associated with the account will also be deleted');
		if (!resp) {
			return false;
		} else {
			deleteAccount(accountId);
		}
	});

	$('.selected-delete').live('click', function () {
		var resp;

		resp = confirm('Are you sure you want to delete the selected accounts? Everything associated with the account will also be deleted');
		if (!resp) {
			return false;
		} else {
			$('.selectable .row input[type=checkbox]:checked').each(function () {
				var accountId;

				accountId = $(this).val();
				deleteAccount(accountId);
			});
		}
	});

	$('.row').live('mouseover', function () {
		$(this).find('.icons img').show();
	});
	$('.row').live('mouseout', function () {
		$(this).find('.icons img').hide();
	});

	$('#form-accounts input').keypress(function (event) {
		if (event.which === 13) {
			event.preventDefault();
			searchForAccounts();
		}
	});

	$('#searchMenu').click(function (event) {
		event.stopPropagation();
	});
	$('#searchLink').click(function (event) {
		event.stopPropagation();
		if ($('#searchMenu').is(':visible')) {
			$('#searchMenu').hide();
		} else {
			$('#searchMenu').show();
			$('#searchMenu').position({
				of: $('#title'),
				my: 'right top',
				at: 'right bottom'
			});
			$('#form-accounts input[name=username]').focus();
		}
	});

	$(document).click(function () {
		if ($('#searchMenu').is(':visible')) {
			$('#searchMenu').hide();
		}
	});

	$('#form-accounts input[type=button]').click(function () {
		$('#searchMenu').hide();
		searchForAccounts();
	});

	searchForAccounts();
});

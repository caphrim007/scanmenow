/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForUrls() {
	var baseUrl, url, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/urls/search';
	params = $('#search, #form-urls').serialize();

	$('.progress').show();

	$.get(
		url,
		params,
		function (data) {
			if (data.status === true) {
				$('.progress').hide();
				$('#url-list').html(data.content);
				$('.icons img').hide();
				$('.block input[type=checkbox]').unbind('click').shiftcheckbox();
			}
		},
		'json'
	);
}

function deleteUrl(urlId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/urls/delete';
	params = {
		'id': urlId
	};

	$.post(
		url,
		params,
		function (data) {
			var searchTimeout;

			if (data.status === true) {
				searchTimeout = $('#form-edit').data('searchTimeout');
				if (searchTimeout !== undefined) {
					clearTimeout(searchTimeout);
				}

				searchTimeout = setTimeout(function () {
					searchForUrls();
				}, 1000);

				$('#form-edit').data('searchTimeout', searchTimeout);
			} else {
				return;
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var searchTimeout, baseUrl, url, params, row, page, oldPage;

	searchTimeout = undefined;
	baseUrl = $('#form-edit input[name=base-url]').val();
	url = undefined;
	params = {};
	row = undefined;
	page = undefined;
	oldPage = undefined;

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
		var urlId, resp;

		urlId = $(this).parents('.row').find('input[name=urlId]').val();
		resp = confirm('Are you sure you want to delete this url? Everything associated with the url will also be deleted');
		if (!resp) {
			return false;
		} else {
			deleteUrl(urlId);
		}
	});

	$('.selected-delete').live('click', function () {
		var resp;

		resp = confirm('Are you sure you want to delete the selected urls? Everything associated with the urls will also be deleted');
		if (!resp) {
			return false;
		} else {
			$('.selectable .row input[type=checkbox]:checked').each(function () {
				var urlId;

				urlId = $(this).val();
				deleteUrl(urlId);
			});
		}
	});

	$('.row').live('mouseover', function () {
		$(this).find('.icons img').show();
	});
	$('.row').live('mouseout', function () {
		$(this).find('.icons img').hide();
	});

	$('.show-more').live('click', function () {
		var page, oldPage, oldPageNum, newPage;

		page = $('#search input[name=page]');
		oldPage = $('#search input[name=old-page]');
		oldPageNum = oldPage.val();
		newPage = parseInt(oldPageNum, 10) + 1;

		oldPage.val(newPage);
		page.val(newPage);
		searchForUrls();
	});
	$('.show-less').live('click', function () {
		var page, oldPage, oldPageNum, newPage;

		page = $('#search input[name=page]');
		oldPage = $('#search input[name=old-page]');
		oldPageNum = oldPage.val();
		newPage = parseInt(oldPageNum, 10) - 1;

		oldPage.val(newPage);
		page.val(newPage);
		searchForUrls();
	});

	$('#form-search input[name=filter]').bind('keyup', function () {
		if (searchTimeout !== undefined) {
			clearTimeout(searchTimeout);
		}

		searchTimeout = setTimeout(function () {
			searchForUrls();
		}, 300);
	});

	searchForUrls();
});

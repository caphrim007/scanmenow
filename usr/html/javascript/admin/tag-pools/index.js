/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForPools() {
	var baseUrl, url;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/tag-pools/search';

	$('.progress').show();

	$.get(
		url,
		$('#search').serialize(),
		function (data) {
			$('.search-results .content').html(data);
			$('.search-results .content').show();
			$('.progress').hide();
			$('.icons img').hide();
			$('.block input[type=checkbox]').unbind('click').shiftcheckbox();
		},
		'html'
	);
}

function deletePool(poolId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/tag-pools/delete';
	params = {
		'id': poolId
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
					searchForPools();
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
	var baseUrl, url, params, row, page, oldPage;

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
		var poolId, resp;

		poolId = $(this).parents('.row').find('input[name=poolId]').val();
		resp = confirm('Are you sure you want to delete this tag pool?');
		if (!resp) {
			return false;
		} else {
			deletePool(poolId);
		}
	});

	$('.selected-delete').live('click', function () {
		var resp;

		resp = confirm('Are you sure you want to delete the selected pools?');
		if (!resp) {
			return false;
		} else {
			$('.selectable .row input[type=checkbox]:checked').each(function () {
				var poolId;

				poolId = $(this).val();
				deletePool(poolId);
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
		searchForPools();
	});
	$('.show-less').live('click', function () {
		var page, oldPage, oldPageNum, newPage;

		page = $('#search input[name=page]');
		oldPage = $('#search input[name=old-page]');
		oldPageNum = oldPage.val();
		newPage = parseInt(oldPageNum, 10) - 1;

		oldPage.val(newPage);
		page.val(newPage);
		searchForPools();
	});

	searchForPools();
});

/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForTags() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/tags/search';
	params = $('#search, #form-tags').serialize();

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
						$('#search input[name=page]').val(pageClickedNumber);
						searchForTags();
					}
				}).show();
			}
		},
		'json'
	);
}

function deleteTag(tagId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/tags/delete';
	params = {
		'id': tagId
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
					searchForTags();
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

	$('#searchMenu').hide();
	$('#form-edit').data('isSearching', false);

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
		var tagId, resp;

		tagId = $(this).parents('.row').find('input[name=tagId]').val();
		resp = confirm('Are you sure you want to delete this tag? Urls using this tag will be untagged');
		if (!resp) {
			return false;
		} else {
			deleteTag(tagId);
		}
	});

	$('.selected-delete').live('click', function () {
		var resp;

		resp = confirm('Are you sure you want to delete the selected tags? Urls using these tags will be untagged');
		if (!resp) {
			return false;
		} else {
			$('.selectable .row input[type=checkbox]:checked').each(function () {
				var tagId;

				tagId = $(this).val();
				deleteTag(tagId);
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
		searchForTags();
	});
	$('.show-less').live('click', function () {
		var page, oldPage, oldPageNum, newPage;

		page = $('#search input[name=page]');
		oldPage = $('#search input[name=old-page]');
		oldPageNum = oldPage.val();
		newPage = parseInt(oldPageNum, 10) - 1;

		oldPage.val(newPage);
		page.val(newPage);
		searchForTags();
	});

	$('#form-tags input').keypress(function (event) {
		if (event.which === 13) {
			event.preventDefault();
			searchForTags();
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
			$('#form-tags input[name=tagName]').focus();
			$('#searchMenu').position({
				of: $('#title'),
				my: 'right top',
				at: 'right bottom'
			});
		}
	});

	$(document).click(function () {
		if ($('#searchMenu').is(':visible')) {
			$('#searchMenu').hide();
		}
	});

	$('#form-tags input[type=button]').click(function () {
		$('#searchMenu').hide();
		searchForTags();
	});

	searchForTags();
});

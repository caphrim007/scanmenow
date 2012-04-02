/*jslint browser: true, devel: true, cap: false, maxerr: 65535, nomen: false*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForTags() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/tables/empty-tags/table';
	params = $('#search').serialize();

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
				$('#totalTagsVal').html(data.totalTags);
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
				searchForTags();
			} else {
				return;
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var baseUrl, dates, from, to;

	baseUrl = $('#form-edit input[name=base-url]').val();
	$('.progress, .message').hide();

	$('.icons img.trash').live('click', function (ev) {
		var tagId, resp;

		tagId = $(this).parents('.row').find('input[name=tagId]').val();
		resp = confirm('Are you sure you want to delete this tag?');
		if (!resp) {
			return false;
		} else {
			deleteTag(tagId);
		}
	});

	$('.row').live('mouseover', function () {
		$(this).find('.icons img').show();
	});
	$('.row').live('mouseout', function () {
		$(this).find('.icons img').hide();
	});

	searchForTags();
});

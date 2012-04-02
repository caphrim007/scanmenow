/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchPermissionAvailable(permission, page, limit) {
	var baseUrl, url, roleId, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/roles/search-permission-available';
	roleId = $('#form-submit input[name=id]').val();

	params = {'type': permission, 'page': page, 'limit': limit, 'id': roleId};
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
			$('#permission-block-' + permission + ' .permissions .span-10 ol.available').append(data);
		},
		'html'
	);

	return true;
}

$(document).ready(function () {
	var baseUrl, searchTimeout;

	baseUrl = $('#form-edit input[name=base-url]').val();
	searchTimeout = undefined;
	$('#form-edit').data('isSearching', false);

	$('.error, .success, .message, .progress').hide();

	$('.available li').live('click', function () {
		var itemClone, topDiv, origImg, origImgSrc, origImgNewSrc,
			origInputName, origInputNewName, selected, existing, img,
			newImgSrc;

		itemClone = $(this).clone();
		topDiv = $(this).parents('div.permissions');

		origImg = $(this).find('img');
		origImgSrc = origImg.attr('src');
		origImgNewSrc = origImgSrc.replace('forward.png', 'forward-selected.png');

		origInputName = itemClone.find('input[type=hidden]').attr('name');
		origInputNewName = origInputName.replace('available', 'selected');

		selected = $(this).attr('class');
		existing = topDiv.find('.selected li[class=' + selected + ']');

		if (existing.size() > 0) {
			return;
		}

		itemClone.find('input[type=hidden]').attr('name', origInputNewName);
		origImg.attr('src', origImgNewSrc);
		img = $(itemClone).find('img');
		newImgSrc = img.attr('src').replace('forward.png', 'back.png');

		img.attr('src', newImgSrc);

		topDiv.find('.selected').append(itemClone);
	});

	$('.add-all').click(function () {
		var topDiv;

		topDiv = $(this).parents('div.permission-block');
		topDiv.find('ol.available li').each(function () {
			$(this).trigger('click');
		});
	});

	$('.clear-all').click(function () {
		var topDiv;

		topDiv = $(this).parents('div.permission-block');
		topDiv.find('ol.selected li').each(function () {
			$(this).trigger('click');
		});
	});

	$('.selected li').live('click', function () {
		var topDiv, selected, existing, origImg, origImgSrc, origImgNewSrc;

		topDiv = $(this).parents('div.permissions');
		selected = $(this).attr('class');
		existing = topDiv.find('.selected li[class=' + selected + ']');

		if (existing.size() > 0) {
			$(this).remove();

			origImg = topDiv.find('.available li[class=' + selected + '] img');
			origImgSrc = origImg.attr('src');
			origImgNewSrc = origImgSrc.replace('forward-selected.png', 'forward.png');
			origImg.attr('src', origImgNewSrc);
		}
	});

	$('#btn-save').click(function () {
		var url, params;

		url = baseUrl + '/admin/roles/save';
		params = $('#form-submit').serialize();

		$('.progress').show();
		$('#btn-save').attr('disabled', true);

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					window.location = baseUrl + '/admin/roles';
				} else {
					$('#btn-save').attr('disabled', false);
					$('.progress').show();
					$('.error').show();
				}
			},
			'json'
		);
	});

	$('#permission-block-tag .selectable').bind('scroll', function () {
		var elem, page, limit, result;

		elem = $('#permission-block-tag .permissions .span-10 ol');
		if (elem[0].scrollHeight - elem.scrollTop() <= elem.outerHeight()) {
			page = $('#permission-block-tag').data('page');
			limit = 10;

			if (page === undefined) {
				// setting to 2 because page one is displayed when the 
				// page first loads
				page = 2;
			}

			result = searchPermissionAvailable('tag', page, limit);
			if (result === true) {
				$('#permission-block-tag').data('page', page + 1);
			}
		}
	});

	$('.queue .selectable').bind('scroll', function () {
		var elem;

		elem = $('.queue .permissions .span-10 ol');
		if (elem[0].scrollHeight - elem.scrollTop() <= elem.outerHeight()) {
			console.log("We're at the bottom of queues!");
		}
	});
});

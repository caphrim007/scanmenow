/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchTags(tag, page) {
	var baseUrl, url, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/whitelist-reconcile/search';
	params = {'filter': tag, 'page': page };

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
			$('#permission-block-tag .permissions ol.available').append(data);
		},
		'html'
	);

	return true;
}

function save() {
	var baseUrl, url, params, isSearching;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/reports/whitelist-reconcile/save';
	params = $('#form-submit').serialize();

	isSearching = $('#form-edit').data('isSearching');
	if (isSearching !== false) {
		return false;
	} else {
		$('#form-edit').data('isSearching', true);
		$('.progress').show();
	}

	$('#form-ops input[type=button]').attr('disabled', true);

	$.post(
		url,
		params,
		function (data) {
			$('#form-ops input[type=button]').attr('disabled', false);
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

$(document).ready(function () {
	var searchTimeout, autocompleteUrl, url, baseUrl;

	searchTimeout = undefined;
	autocompleteUrl = baseUrl + '/urls/tags/search';
	url = $('#form-submit input[name=uri]').val();

	baseUrl = $('#form-edit input[name=base-url]').val();
	$('.progress, .message').hide();

	$('.progress, .message, #mesg-already-exists').hide();
	$('#form-edit').data('isSearching', false);
	$('#form-edit').data('isSaving', false);

	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});

	$('.searchbox').watermark('search...');

	$('#permission-block-tag .selectable').bind('scroll', function () {
		var elem, searchVal, page, search, result;

		elem = $('#permission-block-tag .permissions ol');
		searchVal = $('.searchbox').val();

		if (elem[0].scrollHeight - elem.scrollTop() <= elem.outerHeight()) {
			if ($('.searchbox').is(':visible') && searchVal !== 'search...') {
				page = $('#permission-block-tag').data('page-search');
				search = $('.searchbox').val();

				if (page === undefined || page === 1) {
					// setting to 2 because page one is displayed when the 
					// page first loads
					page = 2;
				}

				result = searchTags(search, page);
				if (result === true) {
					$('#permission-block-tag').data('page-search', page + 1);
				}
			} else if (searchVal === 'search...') {
				page = $('#permission-block-tag').data('page-search');
				search = '*';

				if (page === undefined || page === 1) {
					// setting to 2 because page one is displayed when the 
					// page first loads
					page = 2;
				}

				result = searchTags(search, page);
				if (result === true) {
					$('#permission-block-tag').data('page-search', page + 1);
				}
			}
		}
	});

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
			if (origImgSrc) {
				origImgNewSrc = origImgSrc.replace('forward-selected.png', 'forward.png');
				origImg.attr('src', origImgNewSrc);
			}
		}
	});
	$('.searchbox').bind('keyup', function () {
		if (searchTimeout !== undefined) {
			clearTimeout(searchTimeout);
		}

		searchTimeout = setTimeout(function () {
			var page, search;
			page = 1;
			search = null;
			search = $('.searchbox').val();

			$('#permission-block-tag').data('page-search', page);
			$('#permission-block-tag ol.available').html('');
			searchTags(search, page);
		}, 300);
	});

	$('#form-ops input[type=button]').click(function () {
		save();
	});

	searchTags('*', 1);
});

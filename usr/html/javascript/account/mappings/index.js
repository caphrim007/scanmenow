/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForMappings() {
	var baseUrl, url;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/account/mappings/search';

	$('.progress').show();

	$.get(
		url,
		$('#search').serialize(),
		function (data) {
			var block;

			block = $('.block');

			$('.progress').hide();

			block.find('.search-results .content').html(data);
			block.find('.search-results .content').show();
			block.find('.icons img').hide();
			block.find('input[type=checkbox]').unbind('click').shiftcheckbox();
		},
		'html'
	);
}

function deleteMapping(mappingId) {
	var baseUrl, url, accountId, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/account/mappings/delete';
	accountId = $('#form-edit input[name=accountId]').val();
	params = {
		'accountId': accountId,
		'mapId': mappingId
	};

	$.post(
		url,
		params,
		function (data) {
			if (data.status === true) {
				searchForMappings();
			} else {
				$('.message').hide();
				$('#notices .error').html(data.message);
				$('#notices .error').show();
			}
		},
		'json'
	);
}

$(document).ready(function () {
	$('.role-row img, #create-mapping-body .error').hide();
	$('.message, .trash-icon img').hide();

	$('.role-row').hover(
		function () {
			$(this).find('.trash-icon img').show();
		},
		function () {
			$(this).find('.trash-icon img').hide();
		}
	);

	$('.icons img.trash').live('click', function () {
		var mappingId, resp;

		mappingId = $(this).parents('.row').find('input[name=mapId]').val();
		resp = confirm('Are you sure you want to delete the selected account mappings?');
		if (!resp) {
			return false;
		}

		deleteMapping(mappingId);
	});

	$('.row').live('mouseover', function () {
		$(this).find('.icons img').show();
	});
	$('.row').live('mouseout', function () {
		$(this).find('.icons img').hide();
	});

	$('.select-all').click(function () {
		$('.selectable input[type=checkbox]').attr('checked', true);
	});
	$('.select-none').click(function () {
		$('.selectable input[type=checkbox]').attr('checked', false);
	});
	$('.selected-delete').click(function () {
		var resp;

		resp = confirm('Are you sure you want to delete the selected account mappings?');
		if (!resp) {
			return false;
		}

		$('.selectable .row input[type=checkbox]:checked').each(function () {
			var mappingId;

			mappingId = $(this).val();
			deleteMapping(mappingId);
		});
	});

	searchForMappings();
});

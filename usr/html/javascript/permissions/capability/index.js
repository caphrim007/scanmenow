/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function search() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/permissions/capability/search';
	params = $('#form-edit').serialize();

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
						search();
					}
				}).show();
			}
		},
		'json'
	);
}

function deletePermission(permissionId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/permissions/capability/delete';
	params = {
		'permissionId': permissionId
	};

	$.post(
		url,
		params,
		function (data) {
			if (data.status === true) {
				search();
			} else {
				return;
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var baseUrl;

	baseUrl = $('#form-edit input[name=base-url]').val();
	$('#form-edit').data('isSearching', false);

	$('.message, .progress').hide();

	$('#btn-save').click(function () {
		var url, params;

		url = baseUrl + '/admin/roles/save';
		params = $('#form-submit').serialize();

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					window.location = baseUrl + '/permissions/capability/save';
				} else {
					$('.error').show();
				}
			},
			'json'
		);
	});

	$('#content tr').live('mouseover', function () {
		$(this).find('.icons img').show();
	});

	$('#content tr').live('mouseout', function () {
		$(this).find('.icons img').hide();
	});

	$('.icons img.trash').live('click', function () {
		var permissionId, resp;

		permissionId = $(this).parents('.icons').find('input[name=permissionId]').val();
		resp = confirm('Are you sure you want to delete this permission?');
		if (!resp) {
			return false;
		} else {
			deletePermission(permissionId);
		}
	});

	search();
});

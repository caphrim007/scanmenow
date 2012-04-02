/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForRoles() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/roles/search';
	params = $('#form-edit, #form-roles').serialize();

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
						searchForRoles();
					}
				}).show();
			}
		},
		'json'
	);
}

function deleteRole(roleId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/roles/delete';
	params = {
		'roleId': roleId
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
					searchForRoles();
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
	var baseUrl, searchTimeout;

	baseUrl = $('#form-edit input[name=base-url]').val();
	searchTimeout = undefined;

	$('#searchMenu').hide();

	$('.role-list .progress').hide();
	$('#form-edit').data('isSearching', false);

	$('.role-checkbox').live('click', function () {
		var count;

		count = $('.row input[type=checkbox]:checked').length;
		if (count > 0) {
			$('#with-selected').show();
		} else {
			$('#with-selected').hide();
		}
	});

	$('.selected-delete').live('click', function () {
		var length, resp;

		length = $('.row input[type=checkbox]:checked').length;
		if (length > 0) {
			resp = confirm('Are you sure you want to delete these roles?');
			if (!resp) {
				return false;
			}

			$('.row input[type=checkbox]:checked').each(function () {
				var roleId;

				roleId = $(this).val();
				deleteRole(roleId);
			});
		}
	});

	$('.select-all').click(function () {
		$('.role-checkbox').attr('checked', true);
	});

	$('.select-none').click(function () {
		$('.role-checkbox').attr('checked', false);
	});

	$('.icons img.trash').live('click', function () {
		var roleId, resp;

		roleId = $(this).parents('.row').find('input[name=roleId]').val();
		resp = confirm('Are you sure you want to delete this role?');
		if (!resp) {
			return false;
		}

		deleteRole(roleId);
	});

	$('.row').live('mouseover', function () {
		$(this).find('.icons img').show();
	});
	$('.row').live('mouseout', function () {
		$(this).find('.icons img').hide();
	});

	$('#form-roles input').keypress(function (event) {
		if (event.which === 13) {
			event.preventDefault();
			searchForRoles();
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
			$('#form-roles input[name=username]').focus();
		}
	});

	$(document).click(function () {
		if ($('#searchMenu').is(':visible')) {
			$('#searchMenu').hide();
		}
	});

	$('#form-roles input[type=button]').click(function () {
		$('#searchMenu').hide();
		searchForRoles();
	});

	searchForRoles();
});

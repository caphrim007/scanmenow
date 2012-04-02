/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function searchForQueues() {
	var baseUrl, url;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/queues/search';

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

function deleteQueue(queueId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/admin/queues/delete';
	params = {
		'id': queueId
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
					searchForQueues();
				}, 1000);

				$('#form-edit').data('searchTimeout', searchTimeout);
			} else {
				return;
			}
		},
		'json'
	);
}

function flushQueue(queueId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/queues/messages/flush';
	params = {
		'queueId': queueId
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
					searchForQueues();
				}, 300);

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
		var queueId, resp;

		queueId = $(this).parents('.row').find('input[name=queueId]').val();
		resp = confirm('Are you sure you want to delete this queue? All messages in the queue will also be deleted');
		if (!resp) {
			return false;
		} else {
			deleteQueue(queueId);
		}
	});

	$('.selected-delete').live('click', function () {
		var resp;

		resp = confirm('Are you sure you want to delete the selected queues? All messages in the queues will also be deleted');
		if (!resp) {
			return false;
		} else {
			$('.selectable .row input[type=checkbox]:checked').each(function () {
				var queueId;

				queueId = $(this).val();
				deleteQueue(queueId);
			});
		}
	});

	$('.selected-empty').live('click', function () {
		var resp;

		resp = confirm('Are you sure you want to empty the selected queues? All messages in the queues will be deleted');
		if (!resp) {
			return false;
		} else {
			$('.selectable .row input[type=checkbox]:checked').each(function () {
				var queueId;

				queueId = $(this).val();
				flushQueue(queueId);
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
		searchForQueues();
	});
	$('.show-less').live('click', function () {
		var page, oldPage, oldPageNum, newPage;

		page = $('#search input[name=page]');
		oldPage = $('#search input[name=old-page]');
		oldPageNum = oldPage.val();
		newPage = parseInt(oldPageNum, 10) - 1;

		oldPage.val(newPage);
		page.val(newPage);
		searchForQueues();
	});

	searchForQueues();
});

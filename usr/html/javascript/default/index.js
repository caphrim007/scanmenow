/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function checkStatus(scannerId, callback) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/scanner/ping';
	params = {
		'scannerId': scannerId,
	};

	$('#messageBar .message').hide();

	$.get(
		url,
		params,
		callback,
		'json'
	);
}

function checkScanning(scannerId, callback) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/scanner/scanning';
	params = {
		'scannerId': scannerId,
	};

	$('#messageBar .message').hide();

	$.get(
		url,
		params,
		callback,
		'json'
	);
}

function scheduleScan(scanner, profile) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/scan/';
	params = {
		'scanner': scanner,
		'profile': profile
	};

	$('#messageBar .message').hide();

	$.post(
		url,
		params,
		function (data) {
			if (data.status === true) {
				$('.scanner-block')
					.find('input[value=' + scanner + ']')
					.parents('.scanner-block')
					.find('.scanner-block-header table, .scanner-block-body > div')
					.hide();

				$('.scanner-block')
					.find('input[value=' + scanner + ']')
					.parents('.scanner-block')
					.find('.scanning')
					.show();

				$('#messageBar .info .content').html(data.message);
				$('#messageBar .info').show();
				$('#messageBar .info').effect('highlight');

				setTimeout(function(){
					window.location = baseUrl;
				},3000);
			} else {
				$('#messageBar .error .content').html(data.message);
				$('#messageBar .error').show();
				$('#messageBar .error').effect('highlight');
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var totalScanners, currentScanId, currentFormat;

	totalScanners = 0;

	$('.progress, .wget, .no-wget-links, .wget-links, #positionable').hide();

	$('#get-urls-link').click(function () {
		$('.no-wget, .wget-links').hide();
		$('.wget, .no-wget-links').show();
	});

	$('#get-btn-link').click(function () {
		$('.wget, .no-wget-links').hide();
		$('.no-wget, .wget-links').show();
	});

	$('.selectCmd').click(function () {
		$(this).parents('div.scanner-block-type')
			.find('textarea')
			.focus()
			.select();
	});

	$('button.classy').click(function (event) {
		var area, type;

		scanner = $(this).parents('div.scanner-block')
			.find('input[name=scanner]')
			.val();
		profile = $(this).val();

		scheduleScan(scanner, profile);
	});

	$('.scanner-block .scanner-block-header table').hide();
	$('.scanner-block .scanner-block-header .default').show();

	$('.scanner-block .scanner-block-body > div').hide();
	$('.scanner-block .scanner-block-body .default').show();

	// http://howtonode.org/step-of-conductor
	$('#sans-wget .scanner-block').each(function(){
		var scanner, scannerUiBlock, scannerWgetBlock;

		scanner = $(this).find('input[name=scanner]').val();
		if (empty(scanner)) {
			return;
		}

		scannerUiBlock = $(this);
		scannerWgetBlock = $('#with-wget .scanner-block')
			.find('input[value=' + scanner + ']')
			.parents('.scanner-block');

		Step(
			function checkScannerStatus() {
				return checkStatus(scanner, this);
			},
			function checkForScanning(resp, text) {
				scannerUiBlock.find('.checking').hide();
				scannerWgetBlock.find('.checking').hide();

				scannerUiBlock.find('.default').hide();
				scannerWgetBlock.find('.default').hide();

				if (resp.status == false) {
					scannerUiBlock.find('.notavailable').show();
					scannerWgetBlock.find('.notavailable').show();
				} else {
					scannerUiBlock.find('.checking').show();
					scannerWgetBlock.find('.checking').show();
				}

				return checkScanning(scanner, this);
			},
			function displayScanners(resp, text) {
				if (resp.status == true) {
					totalScanners = totalScanners + 1;
				}

				scannerUiBlock.find('.default').hide();
				scannerWgetBlock.find('.default').hide();

				if (resp.message == 'no') {
					scannerUiBlock.find('.available').show();
					scannerWgetBlock.find('.available').show();
				} else if (resp.message == 'yes') {
					scannerUiBlock.find('.scanning').show();
					scannerWgetBlock.find('.scanning').show();
				} else {
					scannerUiBlock.find('.notavailable').show();
					scannerWgetBlock.find('.notavailable').show();
				}

				return resp.status;
			},
			function displayWget(resp, text) {
				if (totalScanners > 0) {
					$('.wget-links').show();
				}
			}
		);
	});

	$('div.buttons p.button').click(function(){
		$('#positionable')
			.show()
			.position({
				of: $(this),
				my: 'left top',
				at: 'left bottom',
			});

		currentScanId = $(this)
			.siblings('input[name=scanId]')
			.val();
	});

	$(document).mousedown(function () {
		if ($('#positionable').is(':visible')) {
			$('#positionable').hide();
		}
	});

	$('#positionable').mousedown(function (event) {
		event.stopPropagation();
	});

	$('div.reportFormat').click(function(event){
		var baseUrl, url;

		event.stopPropagation();
		$('#positionable').hide();

		currentFormat = $(this)
			.siblings('input[name=format]')
			.val();

		baseUrl = $('#form-edit input[name=base-url]').val();
		url = baseUrl + '/scan/results?id=' + currentScanId + '&format=' + currentFormat;

		window.open(url, '_blank');
	});
});

/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function loadChart() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/charts/country-exposure/chart';
	params = {};

	$('#countryExposure .progress').show();

	$.get(
		url,
		params,
		function (data) {
			$('#countryExposure .progress').hide();

			if (data.status === true) {
				$('#countryExposureChart').gchart(
					$.gchart.map('world', data.message)
				);
				$('#countryExposureChart').gchart('change', {width: 440, height: 220});
			} else {
				$('#countryExposure .message .content').html(data.message);
				$('#countryExposure .message').show();
			}
		},
		'json'
	);
}

function loadTable() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/charts/country-exposure/table';
	params = {};

	$.get(
		url,
		params,
		function (data) {
			$('#tableData').html(data);
			$(".tablesorter").tablesorter({
				sortList: [[1, 1]],
				widgets: ['zebra']
			})
			.tablesorterPager({container: $('#pager')});
		},
		'html'
	);
}

$(document).ready(function () {
	var baseUrl = $('#form-edit input[name=base-url]').val();
	$('.progress, .message').hide();
	loadChart();
	loadTable();
});

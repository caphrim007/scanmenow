/*jslint browser: true, devel: true, cap: false, maxerr: 65535, forin: true*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function loadChart() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/charts/url-activity-last-month/chart';
	params = {};

	$('#urlActivityLastMonthBar .progress').show();

	$.get(
		url,
		params,
		function (data) {
			var values, chart, dataPoints, labels, newValues, i;

			$('#urlActivityLastMonthBar .progress').hide();

			if (data.status === true) {
				values = data.message;
				chart = {};
				dataPoints = [];
				labels = [];
				newValues = [];

				for (i in data.label) {
					labels.push(parseInt(i, 10));
				}

				for (i in values) {
					newValues.push(values[i]);
					if (values[i] === 0) {
						continue;
					} else {
						dataPoints.push($.gchart.marker('flag', '0000FF', 0, i - 1, 10, 'above', data.label[i]));
					}
				}

				chart = $.extend({}, {
					margins: [0, 0, 30, 0],
					type: 'barVert',
					dataLabels: labels,
					encoding: 'scaled',
					series: [
						$.gchart.series('', newValues, '76A4FB')
					],
					markers: dataPoints
				});

				$('#urlActivityLastMonthBarChart').gchart(chart);
			} else {
				$('#urlActivityLastMonthBar .message .content').html(data.message);
				$('#urlActivityLastMonthBar .message').show();
			}
		},
		'json'
	);
}

$(document).ready(function () {
	var baseUrl;

	baseUrl = $('#form-edit input[name=base-url]').val();
	$('.progress, .message').hide();
	loadChart();
});

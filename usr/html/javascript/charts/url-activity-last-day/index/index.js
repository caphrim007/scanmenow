/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function loadChart() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/charts/url-activity-last-day/chart';
	params = {};

	$('#urlActivityLastDayBar .progress').show();

	$.get(
		url,
		params,
		function (data) {
			$('#urlActivityLastDayBar .progress').hide();

			if (data.status === true) {
				var values, chart, dataPoints, labels, i;

				values = data.message;
				chart = {};
				dataPoints = [];
				labels = [	'12am', '1', '2', '3', '4', '5',
							'6', '7', '8', '9', '10', '11',
							'12pm', '1', '2', '3', '4', '5',
							'6', '7', '8', '9', '10', '11'];

				for (i = 0; i < values.length; i += 1) {
					if (values[i] === 0) {
						continue;
					} else {
						dataPoints.push($.gchart.marker('flag', '0000FF', 0, i, 10, 'above', data.label[i]));
					}
				}

				chart = $.extend({}, {
					margins: [0, 0, 30, 0],
					type: 'barVert',
					dataLabels: labels,
					encoding: 'scaled',
					series: [
						$.gchart.series('', values, '76A4FB')
					],
					markers: dataPoints
				});

				$('#urlActivityLastDayBarChart').gchart(chart);
				$('#urlActivityLastDayBarChart').gchart('change', {width: 660, height: 220});
			} else {
				$('#urlActivityLastDayBar .message .content').html(data.message);
				$('#urlActivityLastDayBar .message').show();
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

/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function loadChart() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/charts/url-activity-last-week/chart';
	params = {};

	$('#urlActivityLastWeekScatter .progress').show();

	$.get(
		url,
		params,
		function (data) {
			var series, values, chart, xaxis, yaxis, i;

			$('#urlActivityLastWeekScatter .progress').hide();

			if (data.status === true) {
				series = [[], [], []];
				values = data.message.data;
				chart = {};
				xaxis = [
					'', '12am', '1', '2',
					'3', '4', '5', '6',
					'7', '8', '9', '10',
					'11', '12pm', '1', '2',
					'3', '4', '5', '6', '7',
					'8', '9', '10', '11'
				];
				yaxis = [
					'', 'Sun', 'Mon', 'Tue',
					'Wed', 'Thr', 'Fri', 'Sat'
				];

				for (i = 0; i < values.length; i = i + 1) {
					series[0][i] = values[i][0];
					series[1][i] = values[i][1];
					series[2][i] = values[i][2];
				}

				chart = {
					type: 'scatter',
					series: [
						$.gchart.series('', series[0], '', '', 0, 24),
						$.gchart.series('', series[1], '', '', 0, 6),
						$.gchart.series('', series[2])
					],
					axes: [
						$.gchart.axis('bottom', xaxis),
						$.gchart.axis('left', yaxis)
					],
					markers: [
						$.gchart.marker('circle', '', 1, 1.0, 30.0)
					]
				};

				$('#urlActivityLastWeekScatterChart').gchart(chart);
			} else {
				$('#urlActivityLastWeekScatter .message .content').html(data.message);
				$('#urlActivityLastWeekScatter .message').show();
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

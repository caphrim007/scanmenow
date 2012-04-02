/*jslint browser: true, devel: true, cap: false, maxerr: 65535, bitwise: false*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function loadChart() {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/charts/url-activity-by-user/chart';
	params = {};

	$('#urlActivityByAccountLastWeekStacked .progress').show();

	$.get(
		url,
		params,
		function (data) {
			var values, chart, dataPoints, labels, color, i;

			$('#urlActivityByAccountLastWeekStacked .progress').hide();

			if (data.status === true) {
				values = data.message.result;
				chart = {};
				dataPoints = [];
				labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
				color = null;

				for (i = 0; i < values.length; i = i + 1) {
					color = ('00000' + (Math.random() * 16777216 << 0).toString(16)).substr(-6);
					dataPoints.push($.gchart.series(values[i].label, values[i].points, color));
				}

				chart = $.extend({}, {
					legend: 'right',
					type: 'barVert',
					dataLabels: labels,
					encoding: 'scaled',
					series: dataPoints
				});

				$('#urlActivityByAccountLastWeekStackedChart').gchart(chart);
			} else {
				$('#urlActivityByAccountLastWeekStacked .message .content').html(data.message);
				$('#urlActivityByAccountLastWeekStacked .message').show();
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

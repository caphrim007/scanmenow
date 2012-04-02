/*jslint browser: true, devel: true, cap: false, maxerr: 65535, nomen: false*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function loadTable(startTime, endTime) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/tables/url-age/table';
	params = [{
		name: 'from',
		value: startTime
	},
	{
		name: 'to',
		value: endTime
	}];

	$.get(
		url,
		params,
		function (data) {
			$('#tableData').html(data);
			$(".tablesorter").tablesorter({
				sortList: [[0, 1]],
				widgets: ['zebra']
			})
			.tablesorterPager({container: $('#pager')});

			$('#totalDocsVal').html($('#totalDocs').val());
			$('#totalDocsBlock').show();
		},
		'html'
	);
}

$(document).ready(function () {
	var baseUrl, dates, from, to;

	baseUrl = $('#form-edit input[name=base-url]').val();
	$('.progress, .message').hide();

	dates = $("#from, #to").datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		changeYear: true,
		onSelect: function (selectedDate) {
			var option, timeRange, instance, dDate;

			timeRange = {
				from: 0,
				to: 0
			};
			if (this.id === "from") {
				option = "minDate";
			} else {
				option = "maxDate";
			}

			instance = $(this).data("datepicker");

			dDate = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);

			dates.not(this).datepicker("option", option, dDate);

			$.each(dates, function () {
				var id, dDate, timestamp;

				id = $(this).attr('id');
				dDate = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, $(this).val(), instance.settings);
				timestamp = dDate.getTime();
				timeRange[id] = timestamp;
			});

			loadTable(timeRange.from, timeRange.to);
		}
	});

	from = $.datepicker.parseDate($.datepicker._defaults.dateFormat, $('#from').val());
	from = from.getTime();
	to = $.datepicker.parseDate($.datepicker._defaults.dateFormat, $('#to').val());
	to = to.getTime();

	loadTable(from, to);
});

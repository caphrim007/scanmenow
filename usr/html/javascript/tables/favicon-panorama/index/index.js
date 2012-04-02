/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window,$*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

$(document).ready(function () {
	$('.progress, .message').hide();

	$('.favIcon').live('click', function () {
		$('#dialog img.diagFavIcon').attr('src', $(this).attr('src'));
		$('#dialog').dialog('open');
	});

	$('#dialog').dialog({
		autoOpen: false,
		bgiframe: true,
		dialogClass: 'mapping-dialog',
		draggable: false,
		resizable: false,
		height: 400,
		modal: true,
		width: 630
	});

	$(window).bind('resize', function () {
		if ($('#dialog').is(':visible')) {
			$('#dialog').dialog('option', 'position', $('#dialog').dialog('option', 'position')); 
		}
	});
	$(document).bind('scroll', function () {
		if ($('#dialog').is(':visible')) {
			$('#dialog').dialog('option', 'position', $('#dialog').dialog('option', 'position')); 
		}
	});

	$('.dialog-close').click(function () {
		$('#dialog').dialog('close');
	});
});

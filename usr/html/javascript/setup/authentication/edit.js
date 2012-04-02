/*jslint browser: true, devel: true, cap: false, maxerr: 65535*/
/*global window, $, empty*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

function switchAuthBlock() {
	var index;

	index = $('#auth-type').val();

	$('.authentication-block').hide();
	$('#' + index).show();
}

function getAuthentication(authenticationId) {
	var baseUrl, url, params;

	baseUrl = $('#form-edit input[name=base-url]').val();
	url = baseUrl + '/setup/authentication/edit';
	params = {
		'id': authenticationId
	};

	$.get(
		url,
		params,
		function (data) {
			$('#dialog .content').html(data);
			$('#dialog .message, .authentication-block').hide();
			switchAuthBlock();
		},
		'html'
	);
}

$(document).ready(function () {
	var useEncryption;

	useEncryption = $('.encryption-block input[name=encryption-type]').is(':checked');

	$('#auth-type').live('change', function () {
		switchAuthBlock();
	});

	$('#dialog').dialog({
		autoOpen: false,
		bgiframe: true,
		dialogClass: 'authentication-dialog',
		draggable: false,
		resizable: false,
		height: 500,
		modal: true,
		position: ['center', 'center'],
		width: 830
	});

	$('.dialog-link').click(function () {
		getAuthentication('_new');
		$('#dialog').dialog('open');
	});
	$('.dialog-close').click(function () {
		$('#dialog').dialog('close');
	});

	$('#dialog .btn-save').click(function () {
		var baseUrl, url, method, params;

		baseUrl = $('#form-edit input[name=base-url]').val();
		url = baseUrl + '/setup/authentication/save';
		method = $('#auth-type').val();
		params = $('#form-submit, #' + method + ' :input').serialize();

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					$('#dialog').dialog('close');
					//searchForAuthentication();
				} else {
					$('#dialog .message .content').html(data.message);
					$('#dialog .message').show();
				}
			},
			'json'
		);
	});

	if (useEncryption) {
		$('input[name=use-encryption]').attr('checked', true);
		$('.encryption-block').show();
	} else {
		$('.encryption-block').hide();
	}

	$('input[name=use-encryption]').live('click', function () {
		var block, defaultPort;

		block = $('.encryption-block');
		defaultPort = $('#Ldap input[name=port]').val();
		defaultPort = parseInt(defaultPort, 10);

		if (block.is(':hidden')) {
			block.find('input[value=useSsl]').attr('checked', true);
			block.show();

			if (empty(defaultPort) || defaultPort === 389) {
				$('#Ldap input[name=port]').val('636');
			}
		} else {
			block.hide();
			block.find('input[name=encryption-type]').attr('checked', false);
			if (empty(defaultPort) || defaultPort === 636) {
				$('#Ldap input[name=port]').val('389');
			}
		}
	});

	$('.message .close').live('click', function () {
		$(this).parents('.message').hide();
	});
});

/*jslint browser: true, devel: true, cap: false, maxerr: 65535, forin: true*/
/*global window, $*/
/* vim: set ts=4:sw=4:sts=4smarttab:expandtab:autoindent */

var RandomPassword = {
	characters: [],
	noSim: 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrsuvwxyz23456789~!@#$%^&*_+=-?:',
	similar: '0olt01',

	generate: function (length) {
		var chars, max, i, j, pass, num;

		this.includeLetters();
		this.includeMixedCase();
		this.includeNumbers();
		this.includePunctuation();

		chars = this.array_unique(this.characters);
		chars = this.array_values(chars);
		max = chars.length - 1;

		i = 0;
		j = 0;

		pass = '';

		while (i <= length) {
			num = this.mt_rand(0, max);
			pass = pass + chars[num];
			i = i + 1;
		}

		return pass;
	},

	array_unique: function (inputArr) {
		var key, tmp_arr2, val, local_array_search;

		key = '';
		tmp_arr2 = {};
		val = '';

		local_array_search = function (needle, haystack) {
			var fkey;

			fkey = '';
			for (fkey in haystack) {
				if (haystack.hasOwnProperty(fkey)) {
					if ((haystack[fkey] + '') === (needle + '')) {
						return fkey;
					}
				}
			}
			return false;
		};
 
		for (key in inputArr) {
			if (inputArr.hasOwnProperty(key)) {
				val = inputArr[key];
				if (false === local_array_search(val, tmp_arr2)) {
					tmp_arr2[key] = val;
				}
			}
		}

		return tmp_arr2;
	},

	array_values: function (input) {
		var tmp_arr, cnt, key;

		tmp_arr = [];
		cnt = 0;
		key = '';

		for (key in input) {
			tmp_arr[cnt] = input[key];
			cnt = cnt + 1;
		} 

		return tmp_arr;
	},

	mt_rand: function (min, max) {
		var argc;

		argc = arguments.length;
		if (argc === 0) {
			min = 0;
			max = 2147483647;
		} else if (argc === 1) {
			throw new Error('Warning: mt_rand() expects exactly 2 parameters, 1 given');
		}

		return Math.floor(Math.random() * (max - min + 1)) + min;
	},

	array_merge: function () {
		var args, retObj, k, j, i, retArr, ct;

		args = Array.prototype.slice.call(arguments);
		retObj = {};
		j = 0;
		i = 0;
		retArr = true;

		for (i = 0; i < args.length; i = i + 1) {
			if (!(args[i] instanceof Array)) {
				retArr = false;
				break;
			}
		}
    
		if (retArr) {
			retArr = [];
			for (i = 0; i < args.length; i = i + 1) {
				retArr = retArr.concat(args[i]);
			}
			return retArr;
		}

		ct = 0;
    
		for (i = 0, ct = 0; i < args.length; i = i + 1) {
			if (args[i] instanceof Array) {
				for (j = 0; j < args[i].length; j = j + 1) {
					ct = ct + 1;
					retObj[ct] = args[i][j];
				}
			} else {
				for (k in args[i]) {
					if (args[i].hasOwnProperty(k)) {
						if (parseInt(k, 10) + '' === k) {
							ct = ct + 1;
							retObj[ct] = args[i][k];
						} else {
							retObj[k] = args[i][k];
						}
					}
				}
			}
		}

		return retObj;
	},

	str_split: function (string, split_length) {
		var chunks, pos, len;

		if (split_length === null) {
			split_length = 1;
		}

		if (string === null || split_length < 1) {
			return false;
		}

		string += '';
		chunks = [];
		pos = 0;
		len = string.length;
		while (pos < len) {
			chunks.push(string.slice(pos, pos += split_length));
		}

		return chunks;
	},

	in_array: function (needle, haystack, argStrict) {
		var key, strict;

		key = '';
		strict = !!argStrict;
		if (strict) {
			for (key in haystack) {
				if (haystack[key] === needle) {
					return true;
				}
			}
		} else {
			for (key in haystack) {
				if (haystack[key] === needle) {
					return true;
				}
			}
		}

		return false;
	},

	includeLetters: function () {
		var chars;

		chars = 'abcdefghijklmnopqrstuvwxyz';
		this.characters = this.array_merge(this.characters, this.str_split(chars, 1));
	},

	includeMixedCase: function () {
		var chars;

		chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		this.characters = this.array_merge(this.characters, this.str_split(chars, 1));
	},

	includeNumbers: function () {
		var chars;

		chars = '0123456789';
		this.characters = this.array_merge(this.characters, this.str_split(chars, 1));
	},

	includePunctuation: function () {
		var chars;

		// I'm deliberately not including all possible punctuation here
		chars = '~!@#$%^&*_+=-?:';
		this.characters = this.array_merge(this.characters, this.str_split(chars, 1));
	}
};

$(document).ready(function () {
	var baseUrl, firstBootFlag;

	baseUrl = $('#form-edit input[name=base-url]').val();
	firstBootFlag = parseInt($('#form-edit input[name=first-boot]').val(), 10);

	$('.message, .firstBoot').hide();
	$('.message .close').click(function () {
		$(this).parents('.message').hide();
	});

	$('#form-submit input[name=password]').keypress(function (event) { 
		if (event.which === 13) {
			event.preventDefault();
			$('#btn-save').trigger('click');
			return true;
		}
	});

	$('#btn-save').click(function () {
		var url, params, username;

		url = baseUrl + '/account/modify/save';
		params = $("#form-submit").serialize(); 
		username = $('#form-submit input[name=username]').val();

		if (username === '') {
			$('.message .content').html('The username cannot be empty');
			$('.message').show();
			return false;
		}

		$('.progress').show();
		$('#btn-save').attr('disabled', true);

		$.post(
			url,
			params,
			function (data) {
				if (data.status === true) {
					window.location = baseUrl + '/admin/account';
				} else {
					$('.progress').hide();
					$('#btn-save').attr('disabled', false);
					$('.message .content').html(data.message);
					$('.message').show();
				}
			},
			'json'
		);
	});

	$('#generate').click(function () {
		var password;

		password = RandomPassword.generate(13);
		$('#form-submit input[name=password]').val(password);
	});

	if (firstBootFlag === 1) {
		$('#firstBootOn').show();
	} else {
		$('#firstBootOff').show();
	}

	$('.toggleFirstBoot').click(function () {
		var url, params;

		url = baseUrl + '/account/firstboot/toggle';
		params = $('#form-edit').serialize();

		$.post(
			url,
			params,
			function (data) {
				var current;

				if (data.status === true) {
					current = parseInt(data.current, 10);
					if (current === 1) {
						$('#firstBootOff').hide();
						$('#firstBootOn').show();
					} else {
						$('#firstBootOn').hide();
						$('#firstBootOff').show();
					}
				}
			},
			'json'
		);
	});
});

/**
 * CF7 — máscara de telefone/WhatsApp + validação de e-mail (cliente).
 */
(function () {
	'use strict';

	var MSG = window.vbCf7Form || {};

	function onlyDigits(value) {
		return String(value || '').replace(/\D+/g, '');
	}

	function maskPhone(value) {
		var d = onlyDigits(value).slice(0, 11);
		if (!d) return '';

		if (d.length <= 2) {
			return '(' + d;
		}
		if (d.length <= 6) {
			return '(' + d.slice(0, 2) + ') ' + d.slice(2);
		}
		if (d.length <= 10) {
			return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
		}
		// Celular / WhatsApp: (00) 00000-0000
		return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
	}

	function isValidBrPhone(value) {
		var d = onlyDigits(value);
		if (d.length !== 10 && d.length !== 11) return false;

		var ddd = parseInt(d.slice(0, 2), 10);
		if (ddd < 11 || ddd > 99) return false;

		// Rejeita 0000… / 1111…
		if (/^(\d)\1+$/.test(d)) return false;

		var num = d.slice(2);
		if (!num || /^0+$/.test(num)) return false;

		return true;
	}

	function isValidEmail(value) {
		var v = String(value || '').trim();
		if (!v || /\s/.test(v)) return false;

		// Pessoal (gmail, outlook…) ou profissional (domínio próprio).
		var re =
			/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
		if (!re.test(v)) return false;

		var domain = v.split('@')[1] || '';
		var tld = domain.split('.').pop() || '';
		return tld.length >= 2;
	}

	function phoneInputs(root) {
		return root.querySelectorAll(
			'input.vb-cf7-phone, input[name="your-phone"], input.wpcf7-tel'
		);
	}

	function emailInputs(root) {
		return root.querySelectorAll(
			'input[name="your-email"], input.wpcf7-email'
		);
	}

	function setCustomValidity(el, ok, message) {
		if (!el || typeof el.setCustomValidity !== 'function') return;
		el.setCustomValidity(ok ? '' : message || 'Campo inválido');
	}

	function bindPhone(input) {
		if (!input || input.dataset.vbPhoneBound === '1') return;
		input.dataset.vbPhoneBound = '1';
		input.setAttribute('inputmode', 'tel');
		input.setAttribute('maxlength', '15');
		input.setAttribute('autocomplete', 'tel');

		if (input.value) {
			input.value = maskPhone(input.value);
		}

		input.addEventListener('input', function () {
			var start = input.selectionStart;
			var before = input.value.length;
			input.value = maskPhone(input.value);
			var after = input.value.length;
			if (typeof start === 'number') {
				input.setSelectionRange(
					Math.max(0, start + (after - before)),
					Math.max(0, start + (after - before))
				);
			}
			var ok = !input.value || isValidBrPhone(input.value);
			setCustomValidity(
				input,
				ok,
				MSG.phoneInvalid || 'Telefone inválido'
			);
		});

		input.addEventListener('blur', function () {
			if (!input.value) {
				setCustomValidity(input, true, '');
				return;
			}
			setCustomValidity(
				input,
				isValidBrPhone(input.value),
				MSG.phoneInvalid || 'Telefone inválido'
			);
		});
	}

	function bindEmail(input) {
		if (!input || input.dataset.vbEmailBound === '1') return;
		input.dataset.vbEmailBound = '1';
		input.setAttribute('autocomplete', 'email');

		input.addEventListener('blur', function () {
			if (!input.value) {
				setCustomValidity(input, true, '');
				return;
			}
			setCustomValidity(
				input,
				isValidEmail(input.value),
				MSG.emailInvalid || 'E-mail inválido'
			);
		});

		input.addEventListener('input', function () {
			if (!input.value) {
				setCustomValidity(input, true, '');
				return;
			}
			setCustomValidity(
				input,
				isValidEmail(input.value),
				MSG.emailInvalid || 'E-mail inválido'
			);
		});
	}

	function enhance(root) {
		var scope = root || document;
		phoneInputs(scope).forEach(bindPhone);
		emailInputs(scope).forEach(bindEmail);
	}

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	onReady(function () {
		enhance(document);

		document.addEventListener(
			'wpcf7submit',
			function (e) {
				var form = e.target;
				if (!form) return;

				phoneInputs(form).forEach(function (input) {
					if (input.value && !isValidBrPhone(input.value)) {
						setCustomValidity(
							input,
							false,
							MSG.phoneInvalid || 'Telefone inválido'
						);
						input.reportValidity();
					}
				});

				emailInputs(form).forEach(function (input) {
					if (input.value && !isValidEmail(input.value)) {
						setCustomValidity(
							input,
							false,
							MSG.emailInvalid || 'E-mail inválido'
						);
						input.reportValidity();
					}
				});
			},
			true
		);

		document.addEventListener('wpcf7mailsent', function (e) {
			enhance(e.target);
		});
	});
})();

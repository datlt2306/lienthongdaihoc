/**
 * Google reCAPTCHA Integration Script (v3 & v2)
 *
 * Provides client-side execution, explicit v2 widget rendering,
 * and automatic submission interception for native forms & AJAX handlers.
 *
 * @package lienthongdaihoc
 */
(function () {
	'use strict';

	window.ltdhRecaptchaWidgets = window.ltdhRecaptchaWidgets || {};

	/**
	 * Callback triggered when Google reCAPTCHA v2 script finishes loading with render=explicit.
	 */
	window.ltdhOnRecaptchaLoad = function () {
		if (!window.ltdhRecaptchaConfig || !window.ltdhRecaptchaConfig.siteKey) {
			return;
		}

		var siteKey = window.ltdhRecaptchaConfig.siteKey;
		var containers = document.querySelectorAll('.ltdh-recaptcha-v2');

		containers.forEach(function (el) {
			if (!el.dataset.widgetId && typeof grecaptcha !== 'undefined' && grecaptcha.render) {
				try {
					var widgetId = grecaptcha.render(el, {
						sitekey: siteKey
					});
					el.dataset.widgetId = widgetId;
					window.ltdhRecaptchaWidgets[widgetId] = el;
				} catch (err) {
					console.warn('reCAPTCHA v2 render error:', err);
				}
			}
		});
	};

	/**
	 * Asynchronously retrieve a reCAPTCHA token for submission verification.
	 *
	 * @param {string} action Action identifier for reCAPTCHA v3
	 * @param {HTMLElement} formEl Optional reference to the specific form element
	 * @returns {Promise<string>} Resolves with token string
	 */
	window.ltdhGetRecaptchaToken = function (action, formEl) {
		return new Promise(function (resolve, reject) {
			if (!window.ltdhRecaptchaConfig || !window.ltdhRecaptchaConfig.enabled || !window.ltdhRecaptchaConfig.siteKey) {
				resolve('');
				return;
			}

			var version = window.ltdhRecaptchaConfig.version || 'v3';

			// --- v2 Checkbox Handling ---
			if (version === 'v2') {
				if (typeof grecaptcha === 'undefined') {
					resolve('');
					return;
				}

				var widgetEl = formEl ? formEl.querySelector('.ltdh-recaptcha-v2') : document.querySelector('.ltdh-recaptcha-v2');
				var widgetId = widgetEl ? widgetEl.dataset.widgetId : undefined;
				var response = (typeof widgetId !== 'undefined') ? grecaptcha.getResponse(widgetId) : grecaptcha.getResponse();

				if (!response) {
					reject(new Error('Vui lòng xác nhận ô "Tôi không phải là người máy".'));
					return;
				}

				resolve(response);
				return;
			}

			// --- v3 Invisible Handling ---
			if (typeof grecaptcha === 'undefined') {
				console.warn('Google reCAPTCHA v3 script not loaded or blocked.');
				resolve('');
				return;
			}

			grecaptcha.ready(function () {
				grecaptcha.execute(window.ltdhRecaptchaConfig.siteKey, {
					action: action || 'submit'
				})
				.then(function (token) {
					resolve(token || '');
				})
				.catch(function (err) {
					console.warn('reCAPTCHA v3 execution failed:', err);
					resolve('');
				});
			});
		});
	};

	// --- Automatic Protection for Native HTML POST Forms ---
	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form || !window.ltdhRecaptchaConfig || !window.ltdhRecaptchaConfig.enabled || !window.ltdhRecaptchaConfig.siteKey) {
			return;
		}

		// Skip GET forms (search, filter, etc.)
		var method = (form.getAttribute('method') || form.method || 'GET').toUpperCase();
		if (method === 'GET') {
			return;
		}

		// Skip AJAX forms that handle their own submissions and tokens
		var ajaxFormIds = [
			'ltdh-lead-magnet-form',
			'elig-unified-form',
			'elig-consultation-form',
			'elig-advanced-verify-form'
		];
		if (form.id && ajaxFormIds.indexOf(form.id) !== -1) {
			return;
		}

		var version = window.ltdhRecaptchaConfig.version || 'v3';

		// v2 Verification Check
		if (version === 'v2') {
			var v2El = form.querySelector('.ltdh-recaptcha-v2');
			if (v2El) {
				var widgetId = v2El.dataset.widgetId;
				var response = (typeof widgetId !== 'undefined') ? grecaptcha.getResponse(widgetId) : grecaptcha.getResponse();
				if (!response) {
					e.preventDefault();
					alert('Vui lòng xác nhận ô "Tôi không phải là người máy" trước khi gửi.');
					return;
				}
			}
			return;
		}

		// v3 Invisible Injection
		var tokenInput = form.querySelector('input[name="g-recaptcha-response"]');
		if (!tokenInput) {
			tokenInput = document.createElement('input');
			tokenInput.type = 'hidden';
			tokenInput.name = 'g-recaptcha-response';
			form.appendChild(tokenInput);
		}

		// If token is already present, proceed normally
		if (tokenInput.value) {
			return;
		}

		// Intercept submit, request token, then re-submit
		e.preventDefault();

		var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
		var originalBtnContent = submitBtn ? submitBtn.innerHTML : '';
		if (submitBtn) {
			submitBtn.disabled = true;
		}

		window.ltdhGetRecaptchaToken('native_form', form)
			.then(function (token) {
				tokenInput.value = token;
				if (submitBtn) {
					submitBtn.disabled = false;
				}
				form.submit();
			})
			.catch(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.innerHTML = originalBtnContent;
				}
				form.submit();
			});
	}, false);

	// Attempt v2 render if container was added dynamically or script loaded after DOM ready
	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		if (typeof grecaptcha !== 'undefined' && grecaptcha.render && window.ltdhOnRecaptchaLoad) {
			window.ltdhOnRecaptchaLoad();
		}
	} else {
		document.addEventListener('DOMContentLoaded', function () {
			if (typeof grecaptcha !== 'undefined' && grecaptcha.render && window.ltdhOnRecaptchaLoad) {
				window.ltdhOnRecaptchaLoad();
			}
		});
	}

})();

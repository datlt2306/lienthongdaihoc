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

	var scriptLoaded = false;
	var scriptLoading = false;
	var readyCallbacks = [];

	/**
	 * Dynamically load Google's api.js script on demand or user interaction.
	 */
	function loadGoogleScript(callback) {
		if (typeof callback === 'function') {
			readyCallbacks.push(callback);
		}

		if (scriptLoaded && typeof grecaptcha !== 'undefined') {
			flushReadyCallbacks();
			return;
		}

		if (scriptLoading) {
			return;
		}

		var config = window.ltdhRecaptchaConfig;
		if (!config || !config.enabled || !config.siteKey) {
			flushReadyCallbacks();
			return;
		}

		scriptLoading = true;
		var version = config.version || 'v3';
		var src = (version === 'v2')
			? 'https://www.google.com/recaptcha/api.js?onload=ltdhOnRecaptchaLoad&render=explicit'
			: 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(config.siteKey);

		var script = document.createElement('script');
		script.src = src;
		script.async = true;
		script.defer = true;
		script.setAttribute('fetchpriority', 'low');

		script.onload = function () {
			scriptLoaded = true;
			scriptLoading = false;
			if (typeof grecaptcha !== 'undefined' && grecaptcha.ready) {
				grecaptcha.ready(function () {
					flushReadyCallbacks();
				});
			} else {
				flushReadyCallbacks();
			}
		};

		script.onerror = function () {
			scriptLoading = false;
			console.warn('reCAPTCHA script failed to load.');
			flushReadyCallbacks();
		};

		document.head.appendChild(script);
	}

	function flushReadyCallbacks() {
		while (readyCallbacks.length > 0) {
			var cb = readyCallbacks.shift();
			try { cb(); } catch (err) { console.error('reCAPTCHA callback error:', err); }
		}
	}

	// Trigger lazy script loading on first user interaction or input focus
	var interactionEvents = ['touchstart', 'pointerdown', 'keydown', 'scroll'];
	function onUserInteraction() {
		interactionEvents.forEach(function (ev) {
			window.removeEventListener(ev, onUserInteraction, { passive: true });
		});
		document.removeEventListener('focusin', onFormFocus);
		loadGoogleScript();
	}

	function onFormFocus(e) {
		if (e.target && e.target.matches && e.target.matches('input, textarea, select')) {
			onUserInteraction();
		}
	}

	if (window.ltdhRecaptchaConfig && window.ltdhRecaptchaConfig.enabled && window.ltdhRecaptchaConfig.siteKey) {
		interactionEvents.forEach(function (ev) {
			window.addEventListener(ev, onUserInteraction, { passive: true, once: true });
		});
		document.addEventListener('focusin', onFormFocus, { passive: true, once: true });
	}

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
	window.ltdhRenderRecaptchaWidgets = window.ltdhOnRecaptchaLoad;

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

			function executeVerification() {
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
				if (typeof grecaptcha === 'undefined' || !grecaptcha.execute) {
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
			}

			// Ensure script is loaded before execution
			if (typeof grecaptcha === 'undefined') {
				var loadTimeout = setTimeout(function () {
					console.warn('reCAPTCHA timed out waiting for script, continuing without blocking.');
					resolve('');
				}, 3500);

				loadGoogleScript(function () {
					clearTimeout(loadTimeout);
					executeVerification();
				});
			} else {
				executeVerification();
			}
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

		// Skip Contact Form 7 forms (CF7 handles its own AJAX submissions and reCAPTCHA)
		if ((form.classList && form.classList.contains('wpcf7-form')) || form.querySelector('input[name="_wpcf7"]')) {
			return;
		}

		// Skip AJAX forms that handle their own submissions and tokens
		var ajaxFormIds = [
			'ltdh-lead-magnet-form',
			'elig-unified-form',
			'elig-consultation-form',
			'elig-advanced-verify-form',
			'ltdh-program-review-form'
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

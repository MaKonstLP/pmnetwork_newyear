'use strict';
import Cookies from 'js-cookie';

export default class Main {
	constructor() {
		let self = this;

		// === записываем в куки данные для отправки Calltracking в БД горько START ===
		//запись в куки только внешнего реферера
		let pageReferrer = '';
		//проверяем что это внешний реферер, а не переход внутри страниц сайта
		// if (document.referrer.indexOf(window.location.origin) != -1) { //в этом случае поддомены (например samara.arendazala.net) тоже считаются внешним реферером
		if (document.referrer.indexOf('korporativ-ng.ru') == -1) { // отсекаем так же поддомена, как внешний реферер
			console.log("from external site");
			if (document.referrer) {
				pageReferrer = document.referrer;
			}
			if (Cookies.get('a_ref_0')) {
				Cookies.set('a_ref_1', pageReferrer, { expires: 365 });
			} else {
				Cookies.set('a_ref_0', pageReferrer, { expires: 365 });
			}
		}

		//запись в куки utm_details
		let currentUrl = '';
		if (window.location.href) {
			currentUrl = window.location.href;
		}
		let patternUtm = RegExp('utm_details=([^\&]*)', 'g');
		let utmExist = patternUtm.exec(currentUrl);
		let utm = {};
		if (utmExist) {
			let rows = utmExist[1].split('|');

			for (let i = 0; i < rows.length; i++) {
				let a = rows[i].split(':');
				utm[a[0]] = a[1];
			}
		}

		if (Object.keys(utm).length != 0) {
			let utmJson = JSON.stringify(utm);
			if (Cookies.get('a_utm_0') && Cookies.get('a_utm_0') != '{}') {
				Cookies.set('a_utm_1', utmJson, { expires: 365 });
			} else {
				Cookies.set('a_utm_0', utmJson, { expires: 365 });
			}
		}
		// === записываем в куки данные для отправки Calltracking в БД горько END ===

		$('body').on('click', '[data-seo-control]', function () {
			$(this).closest('[data-seo-text]').addClass('_active');
		});
		var fired = false;

		window.addEventListener('click', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		window.addEventListener('scroll', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		window.addEventListener('mousemove', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		window.addEventListener('touchmove', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		function load_other() {
			setTimeout(function () {
				self.init();
			}, 100);
		}
		//console.log("конструктор");


		//==== отслеживание 404 страниц в метрике START ====
		currentUrl = window.location.href;
		// function UrlExists(url) {
		// 	let http = new XMLHttpRequest();
		// 	http.open('HEAD', url, false);
		// 	http.send();
		// 	if (http.status == 404) {
		// 		return true;
		// 	}
		// }

		function UrlExists(url, callback) {
			let http = new XMLHttpRequest();
			http.open('HEAD', url);
			http.onreadystatechange = function () {
				if (http.readyState === 4) {
					callback(http.status !== 404);
				}
			};
			http.send();
		}

		// if (UrlExists(currentUrl)) {
		UrlExists(currentUrl, function (exists) {
			if (!exists) {
				console.log('22345');
				//инициализация яндекс метрики в случае 404 ошибки (без нее переменная "ym" будет "undefined" и цель не отправиться)
				(function (w, d, s, l, i) {
					w[l] = w[l] || []; w[l].push({
						'gtm.start':
							new Date().getTime(), event: 'gtm.js'
					}); var f = d.getElementsByTagName(s)[0],
						j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
							'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
				})(window, document, 'script', 'dataLayer', 'GTM-PTTPDSK');

				let ref = document.referrer; //записываем в переменную ref значение реферера
				let siteurl = document.location.href; //записываем в переменную siteurl адрес просмотренной страницы
				let visitParams = { NotFoundURL: { [siteurl]: { Реферер: ref } } }; //записываем в переменную visitParams иерархию с параметрами

				//проверка, что метрика инициализорована, каждые 0.1сек
				(function () {
					var ee = setInterval(function () {
						if (typeof window.ym != 'undefined') {
							ym(66603799, 'reachGoal', '404error', visitParams);//достигаем цель на посещение страницы 404 и передаем в параметрах визитов URL-адрес 404 cтраницы и её реферер.
							clearInterval(ee);
						}
					}, 100); // 0,1 секунды ждать
				})();
			}
		});
		// ==== отслеживание 404 страниц в метрике END ====
	}


	init() {
		setTimeout(function () {
			(function (w, d, s, l, i) {
				w[l] = w[l] || []; w[l].push({
					'gtm.start':
						new Date().getTime(), event: 'gtm.js'
				}); var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
						'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-PTTPDSK');
		}, 100);

		// <!-- Global site tag (gtag.js) - Google Analytics -->
		var googletagmanager_js = document.createElement('script');
		googletagmanager_js.src = 'https://www.googletagmanager.com/gtag/js?id=G-5ZKQBX8T9D';
		document.body.appendChild(googletagmanager_js);

		$(".header_phone_button").on("click", this.helpWhithBookingButtonHandler);
		$(".footer_phone_button").on("click", this.helpWhithBookingButtonHandler);
		$(".header_form_popup").on("click", this.closePopUpHandler);
		$('.header_burger').on('click', this.burgerHandler);
		$(".header_city_select").on("click", this.citySelectHandler);
		$(document).mouseup(this.closeCitySelectHandler);
		//$(document).mouseup(this.closeBurgerHandler);

		/* Настройка формы в окне popup */
		var $inputs = $(".header_form_popup .input_wrapper");

		for (var input of $inputs) {
			if ($(input).find("[name='email']").length !== 0
				|| $(input).find("[name='question']").length !== 0) {
				$(input).addClass("_hide");
			}
		}

		$(".header_form_popup .form_title_main").text("Помочь с выбором зала?");
		$(".header_form_popup .form_title_desc").addClass("_hide");
	}

	helpWhithBookingButtonHandler() {
		var $popup = $(".header_form_popup");
		var body = document.querySelector("body");
		if ($popup.hasClass("_hide")) {

			body.dataset.scrollY = self.pageYOffset;
			body.style.top = `-${body.dataset.scrollY}px`;

			$popup.removeClass("_hide");
			$(body).addClass("_modal_active");
			ym(66603799, 'reachGoal', 'headerlink');
			// gtag('event', 'headerlink', {'event_category': 'click', 'eventAction' : 'Roominfo'});
			gtag('event', 'headerlink', { 'event_category': 'click' });
			gtag('event', 'GA4_custom', { 'eventAction': 'headerlink' });
		}
	}

	closePopUpHandler(e) {
		var $popupWrap = $(".header_form_popup");
		var $target = $(e.target);
		var $inputs = $(".header_form_popup input");
		var body = document.querySelector("body");

		if ($target.hasClass("close_button")
			|| $target.hasClass("header_form_popup")
			|| $target.hasClass("header_form_popup_message_close")) {
			$inputs.prop("value", "");
			$inputs.attr("value", "");
			$('.fc-day-number.fc-selected-date').removeClass('fc-selected-date')
			$popupWrap.addClass("_hide");
			$("body").removeClass("_modal_active");
			window.scrollTo(0, body.dataset.scrollY);
		}
	}

	burgerHandler(e) {
		if ($('header').hasClass('_active')) {
			$('header').removeClass('_active');
		}
		else {
			$('header').addClass('_active');
		}
	}

	closeBurgerHandler(e) {
		var $target = $(e.target);
		var $menu = $(".header_menu");

		if (!$menu.is($target)
			&& $menu.has($target).length === 0) {

			if ($('header').hasClass('_active')) {
				$('header').removeClass('_active');
			}
		}
	}

	citySelectHandler(e) {
		var $target = $(e.target);
		var $button = $(".header_city_select");
		var $cityList = $(".city_select_search_wrapper");

		if ($button.is($target)
			|| $button.has($target).length !== 0) {
			$cityList.toggleClass("_hide");
			$button.toggleClass("_active");
		}
	}

	closeCitySelectHandler(e) {
		var $target = $(e.target);
		var $button = $(".header_city_select");
		var $cityList = $(".city_select_search_wrapper");
		var $backButton = $(".back_to_header_menu");

		if (!$button.is($target)
			&& $button.has($target).length === 0
			&& !$cityList.is($target)
			&& $cityList.has($target).length === 0) {
			if (!$cityList.hasClass("_hide")) {
				$cityList.addClass("_hide");
				$button.removeClass("_active");
			}
		}

		if ($backButton.is($target)) {
			$cityList.addClass("_hide");
			$button.removeClass("_active");
		}
	}
}
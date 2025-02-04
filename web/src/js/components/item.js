'use strict';
import Swiper from 'swiper';
import 'slick-carousel';
import * as Lightbox from '../../../node_modules/lightbox2/dist/js/lightbox.js';
import Breadcrumbs from './breadcrumbs';

export default class Item {
	constructor($item) {
		var self = this;
		this.sliders = new Array();
		this.breadcrumbs = new Breadcrumbs();
		self.mobileMode = self.getScrollWidth() < 768 ? true : false;

		$('[data-action="show_phone"]').on("click", function () {
			$(".object_book").addClass("_active");
			$(".object_book_hidden").addClass("_active");
			$(".object_book_interactive_part").removeClass("_hide");
			$(".object_book_send_mail").removeClass("_hide");
			ym(66603799, 'reachGoal', 'showphone');
			dataLayer.push({ 'event': 'event-to-ga', 'eventCategory': 'Search', 'eventAction': 'ShowPhone' });
			gtag('event', 'showphone', { 'event_category': 'Search', 'event_action': 'ShowPhone' });

			// ==== Gorko-calltracking ====
			let phone = $(this).closest('.object_book_hidden').find('.object_real_phone').text();
			if (typeof ym === 'function') {
				self.sendCalltracking(phone);
			} else {
				setTimeout(function () {
					self.sendCalltracking(phone);
				}, 3000);
			}
		});

		//клик по кнопке "Позвонить"
		$('.item-info__btn_call').on('click', function () {
			ym(66603799, 'reachGoal', 'mobilecall');
			dataLayer.push({ 'event': 'event-to-ga', 'eventCategory': 'Order', 'eventAction': 'mobilecall' });
			gtag('event', 'mobilecall', { 'event_category': 'Order', 'event_action': 'mobilecall' });

			// ==== Gorko-calltracking ====
			let phone = $(this).attr('href');
			if (typeof ym === 'function') {
				self.sendCalltracking(phone);
			} else {
				setTimeout(function () {
					self.sendCalltracking(phone);
				}, 3000);
			}
		})

		$('[data-action="show_form"]').on("click", function () {
			$(".object_book_send_mail").addClass("_hide");
			$(".send_restaurant_info").removeClass("_hide");
		});

		$('[data-action="show_mail_sent"]').on("click", function () {
			$(".send_restaurant_info").addClass("_hide");
			$(".object_book_mail_sent").removeClass("_hide");
		});

		$('[data-action="show_form_again"]').on("click", function () {
			$(".object_book_mail_sent").addClass("_hide");
			$(".send_restaurant_info").removeClass("_hide");
		});

		$('[data-title-address]').on('click', function () {
			let map_offset_top = $('.map').offset().top;
			let map_height = $('.map').height();
			let header_height = $('header').height();
			let window_height = $(window).height();
			let scroll_length = map_offset_top - header_height - ((window_height - header_height) / 2) + map_height / 2;
			$('html,body').animate({ scrollTop: scroll_length }, 400);
		});

		$('[data-book-open]').on('click', function () {
			$(this).closest('.object_book_email').addClass('_form');
		})

		$('[data-book-email-reload]').on('click', function () {
			$(this).closest('.object_book_email').removeClass('_success');
			$(this).closest('.object_book_email').addClass('_form');
		})



		// ==== форма быстрого заказа в карточке реста START ====
		$('[data-fast-order-item]').on('click', function () {
			ym(66603799, 'reachGoal', 'bookclick');
			dataLayer.push({ 'event': 'event-to-ga', 'eventCategory': 'Order', 'eventAction': 'bookclick' });
			gtag('event', 'bookclick', { 'event_category': 'Order', 'event_action': 'bookclick' });

			self.showHideFastForm(true);
		})

		$('.form_fast_order').on('click', '.close_button', function () {
			self.showHideFastForm(false);
		})

		$('.form_fast_order').on('click', '[data-success-close]', function () {
			self.showHideFastForm(false);
		})
		// ==== форма быстрого заказа в карточке реста END ====



		var galleryThumbs = new Swiper('.gallery-thumbs', {
			spaceBetween: 10,
			slidesPerView: 5.2,
			slidesPerColumn: 2,
			freeMode: true,
			watchSlidesVisibility: true,
			watchSlidesProgress: true,
			// Disable preloading of all images
			// preloadImages: false,
			// Enable lazy loading
			// lazy: true,

			breakpoints: {
				767: {
					slidesPerView: 3,
					slidesPerColumn: 1
				}
			}
		});
		var galleryTop = new Swiper('.gallery-top', {
			spaceBetween: 10,
			// Disable preloading of all images
			// preloadImages: false,
			// Enable lazy loading
			// lazy: true,
			thumbs: {
				swiper: galleryThumbs
			}
		});

		$('.object_gallery._room').each((t, e) => {
			let galleryRoomThumbs = new Swiper($(e).find('.gallery-thumbs-room'), {
				//el: ".gallery-thumbs-room",
				spaceBetween: 10,
				slidesPerView: 5.2,
				slidesPerColumn: 1,
				freeMode: true,
				watchSlidesVisibility: true,
				watchSlidesProgress: true,
				// Disable preloading of all images
				// preloadImages: false,
				// Enable lazy loading
				// lazy: true,

				breakpoints: {
					767: {
						slidesPerView: 3,
						slidesPerColumn: 1
					}
				}
			});
			let galleryRoomTop = new Swiper($(e).find('.gallery-top-room'), {
				spaceBetween: 10,
				// Disable preloading of all images
				// preloadImages: false,
				// Enable lazy loading
				// lazy: true,
				thumbs: {
					swiper: galleryRoomThumbs
				}
			});

			this.sliders.push(galleryRoomThumbs);
			this.sliders.push(galleryRoomTop)
		});

		let block_show = null;
		function scrollTracking() {
			const wt = $(window).scrollTop();
			const et = $('.item-info__btns').offset().top;

			if (wt >= et) {
				if (block_show == null || block_show == true) {
					$('.display_bottom').addClass('_active');
					$('.footer_wrap').css('margin-bottom', '70px');
					$('.content_wrap').css('z-index', '10');
				}
				block_show = false;
			} else {
				if (block_show == null || block_show == false) {
					$('.display_bottom').removeClass('_active');
					$('.footer_wrap').css('margin-bottom', '0');
					$('.content_wrap').css('z-index', '0');
				}
				block_show = true;
			}
		}

		$(window).on('scroll', function () {
			if (self.mobileMode) {
				scrollTracking();
			}
		});

		window.onload = function () {
			if (self.mobileMode) {
				scrollTracking();
			}
		};

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
	}



	init() {
		setTimeout(function () {
			const itemReviewBlock = document.querySelector('.item__review');
			if (itemReviewBlock) {
				const restYaId = itemReviewBlock.dataset.restYaId;
				const yndexIframe = `<iframe style="width:100%;height:100%;border:1px solid #e6e6e6;border-radius:10px;box-sizing:border-box" src="https://yandex.ru/maps-reviews-widget/${restYaId}?comments"></iframe>`;
	
				itemReviewBlock.insertAdjacentHTML('afterbegin', yndexIframe);
			}
		}, 100);
	}

	showHideFastForm(show) {
		if (show) {
			$('.form_fast_order').removeClass('_hide');
			$('.fast-order').removeClass('_hide');
			$('body').addClass('_modal_active');
		} else {
			$('.form_fast_order').addClass('_hide');
			$('.form_success_message_sent').addClass('_hide');
			$('body').removeClass('_modal_active');
		}
	}

	getScrollWidth() {
		return Math.max(
			document.body.scrollWidth, document.documentElement.scrollWidth,
			document.body.offsetWidth, document.documentElement.offsetWidth,
			document.body.clientWidth, document.documentElement.clientWidth
		);
	};

	sendCalltracking(phone) {
		let clientId = '';
		ga.getAll().forEach((tracker) => {
			clientId = tracker.get('clientId');
		})

		let yaClientId = '';
		ym(66603799, 'getClientID', function (id) {
			yaClientId = id;
		});

		const data = new FormData();

		if (this.mobileMode) {
			data.append('isMobile', 1);
		}
		data.append('phone', phone);
		data.append('clientId', clientId);
		data.append('yaClientId', yaClientId);

		$.ajax({
			type: 'post',
			url: '/ajax/send-calltracking/',
			data: data,
			processData: false,
			contentType: false,
			success: function (response) {
				// response = $.parseJSON(response);
				// response = JSON.parse(response);
				// self.resolve(response);
				console.log('calltracking sent');
			},
			error: function (response) {
				console.log('calltracking ERROR');
			}
		});

		if ($('[data-premium-rest]').length > 0) {
			let data = new FormData();
			data.append('gorko_id', $('[data-premium-rest]').data('premium-rest'));
			data.append('channel', $('[data-channel-id]').data('channel-id'));
			fetch('/premium/premium-click/', {
				method: 'POST',
				body: data,
			})
				.then((response) => response.json())
				.then((data) => {
					console.log(data);
				})
				.catch((error) => {
					console.error('Error:', error);
				});
		}
	}
}
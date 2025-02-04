'use strict';
import Filter from './filter';
import YaMapAll from './map';
import Breadcrumbs from './breadcrumbs';
import Swiper from 'swiper';
import Cookies from 'js-cookie';

export default class Listing {
	constructor($block) {
		self = this;
		this.block = $block;
		this.filter = new Filter($('[data-filter-wrapper]'));
		this.yaMap = new YaMapAll(this.filter);
		this.breadcrumbs = new Breadcrumbs();
		self.mobileMode = self.getScrollWidth() < 768 ? true : false;

		//КЛИК ПО КНОПКЕ "ПОДОБРАТЬ"
		$('[data-filter-button]').on('click', function () {
			self.reloadListing();
			gtag('event', 'filter', { 'event_category': 'Search', 'event_action': 'Filter' });
		});

		//КЛИК ПО ПАГИНАЦИИ
		$('body').on('click', '[data-pagination-wrapper] [data-listing-pagitem]', function (e) {
			e.preventDefault();
			self.reloadListing($(this).data('page-id'));
		});


		// ==== форма быстрого заказа в листинге START ====
		$('[data-listing-list]').on('click', '[data-fast-order]', function () {
			let restaurantName = $(this).closest('.item').data('rest-name');
			let restaurantImage = $(this).closest('.item').data('rest-img');
			if ($(this).closest('[data-premium-listing-rest]').length) {
				var premium_gorko_id = $(this).closest('[data-premium-listing-rest]').data('premium-listing-rest');
			}
			else {
				var premium_gorko_id = 0;
			}
			let minCapacity = $(this).closest('.item').data('min-capacity');
			let maxCapacity = $(this).closest('.item').data('max-capacity');
			let formSelectItems = $('[data-form-select-item]');

			console.log(premium_gorko_id);

			$('.form_fast_order').find('.form_title_name').text(restaurantName);
			$('.form_fast_order').find('.form_title_img img').attr('src', restaurantImage);
			$('.form_fast_order form').data('premium-listing-form-rest', premium_gorko_id);

			formSelectItems.hide();
			formSelectItems.removeClass('_show');

			formSelectItems.each(function (index, value) {
				if (maxCapacity > $(this).data('min-val')) {
					$(this).addClass('_show');
					$(this).show();
				}
				if (minCapacity > $(this).data('max-val')) {
					$(this).removeClass('_show');
					$(this).hide();
				}
				if (minCapacity == maxCapacity) {
					if (maxCapacity > $(this).data('min-val')) {
						$(this).addClass('_show');
						$(this).show();
					}
					if ($(this).data('max-val') == 15) {
						$(this).removeClass('_show');
						$(this).hide();
					}
				}
			});

			let activeSelectItem = $('[data-form-select-item]._show').first().find('p').text();

			$('#guests_number').val(activeSelectItem);
			$('[data-form-select-current] p').text(activeSelectItem);

			setTimeout(self.showHideFastForm(true), 350);
		})

		$('.form_fast_order').on('click', '.close_button', function () {
			self.showHideFastForm(false);
		})

		$('.form_fast_order').on('click', '[data-success-close]', function () {
			self.showHideFastForm(false);
		})

		//КЛИК ПО БЛОКУ С СЕЛЕКТОМ
		$('.fast-order').on('click', '[data-form-select-current]', function () {
			let $parent = $(this).closest('[data-form-select-block]');
			self.selectBlockClick($parent);
		});

		//КЛИК ПО СТРОКЕ В СЕЛЕКТЕ
		$('.fast-order').on('click', '[data-form-select-item]', function () {
			let selectText = $(this).find('p').text();

			$('[data-form-select-item]').not(this).removeClass('_active');
			$(this).toggleClass('_active');
			self.selectBlockActiveClose();
			$('[data-form-select-current] p').text(selectText);
			$('#guests_number').val(selectText);
		});

		//КЛИК ВНЕ БЛОКА С СЕЛЕКТОМ
		$('body').on('click', function (e) {
			if (!$(e.target).closest('.form_select_block').length) {
				self.selectBlockActiveClose();
			}
		});
		// ==== форма быстрого заказа в листинге END ====

		self.initRoomsSlider();

		$('[data-listing-wrapper]').on('click', '[data-mobile-call]', function () {
			ym(66603799, 'reachGoal', 'mobilecall');
			dataLayer.push({ 'event': 'event-to-ga', 'eventCategory': 'Order', 'eventAction': 'mobilecall' });
			gtag('event', 'mobilecall', { 'event_category': 'Order', 'event_action': 'mobilecall' });

			// ==== Gorko-calltracking ====
			let phone = $(this).data('mobile-call');
			if (typeof ym === 'function') {
				self.sendCalltracking(phone);
			} else {
				setTimeout(function () {
					self.sendCalltracking(phone);
				}, 3000);
			}
			let premium = $(this).parents('[data-premium-listing-rest]');
			console.log(premium);
			if (premium.length) {
				let data = new FormData();
				data.append('gorko_id', premium.data('premium-listing-rest'));
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
		})
	}

	reloadListing(page = 1) {
		let self = this;
		self.block.addClass('_loading');
		self.filter.filterListingSubmit(page);
		self.filter.promise.then(
			response => {
				console.log(self.filter);
				// console.log(response.urlForPagination);
				ym(66603799, 'reachGoal', 'filter');
				dataLayer.push({ 'event': 'event-to-ga', 'eventCategory': 'Search', 'eventAction': 'Filter' });
				$('[data-listing-list]').html(response.listing);
				$('[data-listing-title]').html(response.title);
				$('[data-listing-text-top]').html(response.text_top);
				$('[data-listing-text-bottom]').html(response.text_bottom);
				$('[data-pagination-wrapper]').html(response.pagination);
				$('[data-filter-budget-input]').attr('placeholder', 'от ' + response.minPrice.toLocaleString('ru-RU'));
				self.block.removeClass('_loading');
				$('html,body').animate({ scrollTop: $('.items_list').offset().top - 160 }, 400);
				// history.pushState(historyState, '', '/ploshhadki/'+response.url);
				history.pushState(null, null, '/ploshhadki/' + response.url);
				self.breadcrumbs = new Breadcrumbs();

				self.initRoomsSlider();
			}
		);
	}

	selectBlockClick($block) {
		if ($block.hasClass('_active')) {
			this.selectBlockClose($block);
		} else {
			this.selectBlockOpen($block);
		}
	}

	selectBlockClose($block) {
		$block.removeClass('_active');
	}

	selectBlockOpen($block) {
		$block.addClass('_active');
	}

	selectBlockActiveClose() {
		$('.fast-order').find('[data-form-select-block]._active').each(function () {
			$(this).removeClass('_active');
		});
	}

	initRoomsSlider() {
		document.querySelectorAll('.rooms-slider').forEach(elem => {
			const swiperSlides = elem.querySelectorAll('.swiper-slide');
		
			let slidesPerView = 1;
			if (self.getScrollWidth() <= 500 && swiperSlides.length > 1) {
				slidesPerView = 1.1;
				elem.style.width = 'calc(100% + 15px)';
			}

			var swiper = new Swiper(elem, {
				slidesPerView: 'auto',
				spaceBetween: 10,
				slidesPerView: 1,
				breakpoints: {
					// when window width is <= 500px
					500: {
						slidesPerView: slidesPerView,
					},
				},
				watchOverflow: true,
				// loop: true,
				// pagination: {
				// 	el: '.swiper-pagination',
				// 	clickable: true,
				// },
			});
		});
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
	}
}
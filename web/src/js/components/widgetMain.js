"use strict";

import Swiper from 'swiper';

export default class WidgetMain {
	constructor() {
		this.init();
	}

	init() {
		var swiper = new Swiper('.swiper-container', {
			slidesPerView: "auto",
			//spaceBetween: 30,
			// watchOverflow: true,
			// slidesPerGroup: 1,
			// Disable preloading of all images
			preloadImages: false,
			// Enable lazy loading
			lazy: true,
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			pagination: {
				el: '.swiper-pagination',
				clickable: true,
			},

			breakpoints: {
				// when window width is <= 768px
				768: {
					// autoHeight: true,
				},
			}
		});
	}
}
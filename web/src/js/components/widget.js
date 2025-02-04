'use strict';
import Swiper from 'swiper';

export default class Widget{
	constructor(){
		self = this;
		this.swiperArr = [];

		/* if($(window).width() <= 1650){
			$('[data-widget-wrapper]').each(function(){
				self.initSwiper($(this).find('[data-listing-wrapper]'));
			});
		}

		$(window).on('resize', function(){
			console.log(self.swiperArr.length);
			if($(window).width() <= 1650){
				if(self.swiperArr.length == 0){
					$('[data-widget-wrapper]').each(function(){
						self.initSwiper($(this).find('[data-listing-wrapper]'));
					});
				}
			}
			else{
				$.each(self.swiperArr, function(){
					this.destroy(true, true);
				});
				self.swiperArr = [];
			}
		}); */


	}

	initSwiper($container){
		let swiper = new Swiper($container, {
	        slidesPerView: 3,
	        spaceBetween: 30,
	        navigation: {
              nextEl: '.listing_widget_arrow._next',
              prevEl: '.listing_widget_arrow._prev',
            },
            pagination: {
              el: '.listing_widget_pagination',
              type: 'bullets',
            },
	        breakpoints: {
	        	1200:{
	        		slidesPerView: 2,
	        	},
	        	768:{
	        		slidesPerView: 1,

	        		navigation: false,
	        	}
	        }
	    });

	    let swiper_var = $container.swiper;
		this.swiperArr.push(swiper);
	}
}
'use strict';
import Swiper from 'swiper';

export default class Post{
	constructor($block){
		self = this;
		this.block = $block;
		this.swipers_gal = [];
		this.swipers_rest = [];

		//формируем ссылки внутри текста поста с учетом поддомена сохранненого в куки
		let cookieCityName = document.querySelector('[data-cookie-city-name]').getAttribute('data-cookie-city-name');
		if (cookieCityName.length > 0) {
			let cookieSubdomainName = document.querySelector('[data-cookie-subdomain]').getAttribute('data-cookie-subdomain');
			let links = document.querySelectorAll('.blog_text a');

			links.forEach(function (linkElement) {
				var hrefValue = linkElement.getAttribute('href');
				var newHrefValue = `https://${cookieSubdomainName}${hrefValue}`;
				linkElement.setAttribute('href', newHrefValue);
			});
		}

		$('[data-action="show_phone"]').on("click", function(){
			let $object_book = $(this).closest(".object_book");
			$object_book.addClass("_active");
			$object_book.find(".object_book_hidden").addClass("_active");
			$object_book.find(".object_book_interactive_part").removeClass("_hide");
			$object_book.find(".object_book_send_mail").removeClass("_hide");
			ym(66603799,'reachGoal','showphone');
			dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Search', 'eventAction' : 'ShowPhone'});
		});

		$('[data-action="show_form"]').on("click", function(){
			$(".object_book_send_mail").addClass("_hide");
			$(".send_restaurant_info").removeClass("_hide");
		});

		$('[data-action="show_mail_sent"]').on("click", function(){
			let $object_book = $(this).closest(".object_book");
			$object_book.find(".send_restaurant_info").addClass("_hide");
			$object_book.find(".object_book_mail_sent").removeClass("_hide");
		});

		$('[data-action="show_form_again"]').on("click", function(){
			let $object_book = $(this).closest(".object_book");
			$object_book.find(".object_book_mail_sent").addClass("_hide");
			$object_book.find(".send_restaurant_info").removeClass("_hide");
		});

		$('[data-book-open]').on('click', function(){
            $(this).closest('.object_book_email').addClass('_form');
        })

        $('[data-book-email-reload]').on('click', function(){
            $(this).closest('.object_book_email').removeClass('_success');
            $(this).closest('.object_book_email').addClass('_form');
        })
		
		$('.post_gallery_wrap').each(function(iter,object){
			let postGalleryThumbs = new Swiper($(this).find('.post-gallery-thumbs'), {
		        spaceBetween: 5,
		        slidesPerView: 7,
		        slidesPerColumn: 1,
		        freeMode: true,
		        watchSlidesVisibility: true,
		        watchSlidesProgress: true,

		        breakpoints: {
		            1440: {
		              	slidesPerView: 5,
		            },

		            767: {
		              	slidesPerView: 4,
		            }
		        }
		     });
			let postGalleryTop = new Swiper($(this).find('.post-gallery-top'), {
				spaceBetween: 0,
				thumbs: {
					swiper: postGalleryThumbs
				}
			});

			self.swipers_gal.push({
				postGalleryThumbs,
				postGalleryTop
			});
		});

		$('.post_item_gallery_wrap').each(function(iter,object){
			let postGalleryThumbs = new Swiper($(this).find('.post-item-gallery-thumbs'), {
		        spaceBetween: 5,
		        slidesPerView: 4,
		        slidesPerColumn: 1,
		        freeMode: true,
		        watchSlidesVisibility: true,
		        watchSlidesProgress: true,

		        breakpoints: {
		            1440: {
		              	slidesPerView: 3,
		            },

		            767: {
		              	slidesPerView: 2,
		            }
		        }
		     });
			let postGalleryTop = new Swiper($(this).find('.post-item-gallery-top'), {
				spaceBetween: 0,
				thumbs: {
					swiper: postGalleryThumbs
				}
			});

			self.swipers_rest.push({
				postGalleryThumbs,
				postGalleryTop
			});
		});

		
	}
}
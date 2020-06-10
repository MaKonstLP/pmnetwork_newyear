'use strict';
import Swiper from 'swiper';
import 'slick-carousel';

export default class Item{
	constructor($item){
		var self = this;
		
		$('[data-action="show_phone"]').on("click", function(){
			$(".object_book").addClass("_active");
			$(".object_book_hidden").addClass("_active");
			$(".object_book_interactive_part").removeClass("_hide");
			$(".object_book_send_mail").removeClass("_hide");
		});

		$('[data-action="show_form"]').on("click", function(){
			$(".object_book_send_mail").addClass("_hide");
			$(".send_restaurant_info").removeClass("_hide");
		});

		$('[data-action="show_mail_sent"]').on("click", function(){
			$(".send_restaurant_info").addClass("_hide");
			$(".object_book_mail_sent").removeClass("_hide");
		});

		$('[data-action="show_form_again"]').on("click", function(){
			$(".object_book_mail_sent").addClass("_hide");
			$(".send_restaurant_info").removeClass("_hide");
		});
		
		this.initGallery();
		$(".swiper-container.gallery-thumbs").on("click", this.setActiveNail);
		$(".swiper-container.gallery-thumbs-room").on("click", this.setActiveNail);
		$(".swiper-container.post-gallery-thumbs").on("click", this.setActiveNail);
	}

	getIMGFromGallery(e) {
		let targetIMG = e.target;
		let targetIMGSrc = targetIMG.getAttribute("src");

		if(targetIMG.nodeName == "IMG"){
			$(".object_gallery_other").find("._active").toggleClass("_active");

			targetIMG.classList.toggle("_active");

			$(targetIMG).parent().parent()
			.children().find("img").first().attr("src", targetIMGSrc);
		}
	}

	initGallery(){
		let $firstIMG = $(".object_gallery_other").find("img").first();
		let firstIMGSrc = $firstIMG.attr("src");

		if( !$firstIMG.hasClass("_active") ){
			$firstIMG.addClass("_active");
		}

		$(".object_gallery_main").find("img").attr("src", firstIMGSrc);
		$(".object_gallery_other").on("click", this.getIMGFromGallery);
	}

	setActiveNail(e){
		if ( $(e.target).hasClass("swiper-slide") ){
			$(".swiper-slide-thumb-active").removeClass("swiper-slide-thumb-active");
			$(e.target).addClass("swiper-slide-thumb-active");
		}
	}
}
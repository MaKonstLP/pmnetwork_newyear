'use strict';
import Swiper from 'swiper';
import 'slick-carousel';

export default class Item{
	constructor($item){
		var self = this;
		
		$('[data-action="show_phone"]').on("click", function(){
			$(".object_book_hidden").addClass("_active");
		});

		this.initGallery();
	}

	getIMGFromGallery(e) {
		let targetIMG = e.target;
		let targetIMGSrc = targetIMG.getAttribute("src");

		$(".object_gallery_other").find("._active").toggleClass("_active");

		if(targetIMG.nodeName == "IMG"){
			targetIMG.classList.toggle("_active");
		}

		$(targetIMG).parent().parent()
		.children().find("img").first().attr("src", targetIMGSrc);
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
}
'use strict';
import Swiper from 'swiper';
import 'slick-carousel';

export default class Item{
	constructor($item){
		var self = this;
		this.bigIMG = document.querySelector(".object_gallery_main");
		this.listOfIMG = document.querySelector(".object_gallery_other");
		
		$('[data-action="show_phone"]').on("click", function(){
			$(".object_book_hidden").addClass("_active");
		});

		this.initGallery();
	}

	getIMGFromGallery(e) {
		let targetIMG = e.target;

		console.log(`${targetIMG.getAttribute("src")}`);
		document.querySelector(".object_gallery_other").querySelector("._active").classList.toggle("_active");

		if(targetIMG.nodeName == "IMG"){
			targetIMG.classList.toggle("_active");
		}
		let targetIMGSrc = targetIMG.getAttribute("src");
		document.querySelector(".object_gallery_main").querySelector("img").setAttribute("src", targetIMGSrc);
	}

	initGallery(){
		let firstIMG = this.listOfIMG.querySelector("img");

		if( !firstIMG.classList.contains("_active") ){
			firstIMG.classList.add("_active");
		}

		let firstIMGSrc = firstIMG.getAttribute("src");

		document.querySelector(".object_gallery_main").querySelector("img").setAttribute("src", firstIMGSrc);

		$(".object_gallery_other").on("click", this.getIMGFromGallery);
	}
}
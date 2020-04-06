'use strict';

export default class Main{
	constructor(){
		$('body').on('click', '[data-seo-control]', function(){
			$(this).closest('[data-seo-text]').addClass('_active');
		});
		this.init();
		//console.log("конструктор");
	}


	init() {
		$(".header_phone_button").on("click", this.helpWhithBookingButtonHandler);
		$(".header_form_popup").on("click", this.closePopUpHandler);
		$('.header_burger').on('click', this.burgerHandler);
	
		/* Настройка формы в окне popup */
		let $inputs = $(".header_form_popup .input_wrapper");

		for (let input of $inputs){
			if( $(input).find("[name='email']").length !== 0
			||  $(input).find("[name='question']").length !== 0 ) {
				$(input).addClass("_hide");
			}
		}

		$(".header_form_popup .form_title_main").text("Помочь с выбором зала?");
		$(".header_form_popup .form_title_desc").addClass("_hide");
	}
	helpWhithBookingButtonHandler() {
		let $popup = $(".header_form_popup");
		if ($popup.hasClass("_hide")) {
			$popup.removeClass("_hide");
		}
	}

	closePopUpHandler(e) {
		let $popupWrap = $(".header_form_popup");
		let $target = $(e.target);
		let $inputs = $(".header_form_popup input");

		if( $target.hasClass("close_button")
		 || $target.hasClass("header_form_popup") )  {
			$inputs.prop("value", "");
			$popupWrap.addClass("_hide");
		}	
	}

	burgerHandler(e) {
		if($('header').hasClass('_active')){
			$('header').removeClass('_active');
		}
		else{
			$('header').addClass('_active');
		}
	}
}
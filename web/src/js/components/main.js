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
		$(".footer_phone_button").on("click", this.helpWhithBookingButtonHandler);
		$(".header_form_popup").on("click", this.closePopUpHandler);
		$('.header_burger').on('click', this.burgerHandler);
		$(".header_city_select").on("click", this.citySelectHandler);
		$(document).mouseup(this.closeCitySelectHandler);
		$(document).mouseup(this.closeBurgerHandler);
	
		/* Настройка формы в окне popup */
		var $inputs = $(".header_form_popup .input_wrapper");

		for (var input of $inputs){
			if( $(input).find("[name='email']").length !== 0
			||  $(input).find("[name='question']").length !== 0 ) {
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
		}
	}

	closePopUpHandler(e) {
		var $popupWrap = $(".header_form_popup");
		var $target = $(e.target);
		var $inputs = $(".header_form_popup input");
		var body = document.querySelector("body");

		if( $target.hasClass("close_button")
		 || $target.hasClass("header_form_popup") 
		 || $target.hasClass("header_form_popup_message_close") ) {
			$inputs.prop("value", "");
			$popupWrap.addClass("_hide");
			$("body").removeClass("_modal_active");
			window.scrollTo(0, body.dataset.scrollY);
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

	closeBurgerHandler(e){
		var $target = $(e.target);
		var $menu = $(".header_menu");

		if( !$menu.is($target)
		&& $menu.has($target).length === 0) {

			if($('header').hasClass('_active')){
				$('header').removeClass('_active');
			}
		}

	}

	citySelectHandler(e){
		var $target = $(e.target);
		var $button = $(".header_city_select");
		var $cityList = $(".city_select_search_wrapper");

		if( $button.is($target)
		 || $button.has($target).length !== 0) {
			$cityList.toggleClass("_hide");
			$button.toggleClass("_active");
		}
		 
	}

	closeCitySelectHandler(e){
		var $target = $(e.target);
		var $button = $(".header_city_select");
		var $cityList = $(".city_select_search_wrapper");
		var $backButton = $(".back_to_header_menu");

		if( !$button.is($target)
		&& $button.has($target).length === 0
		&& !$cityList.is($target)
		&& $cityList.has($target).length === 0){
			if ( !$cityList.hasClass("_hide") ){
				$cityList.addClass("_hide");
				$button.removeClass("_active");
			}
		}

		if ( $backButton.is($target)){
			$cityList.addClass("_hide");
			$button.removeClass("_active");
		}
	}
}
"use strict";

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';

export default class CalendarCustom{
  constructor(calendarEl){
		this.init(calendarEl);
		this.initCalendarButtons();
		this.initFooter(calendarEl);
  }

  init(calendarEl) {
		let calendar = new Calendar(calendarEl, {
			firstDay: 1,
			defaultDate: '2021-12-01',
			locale: ruLocale,
			aspectRatio: 1.35,
			height: 230,
			header: {
				left: "title",
				right: "prev,next"
			},

			plugins: [ dayGridPlugin, interactionPlugin ],

			dateClick: function(info){
				let numInCell = info.jsEvent.target;
				let selectedDate = info.dateStr;
				let $button = $(".fc-booking-button");
				//console.log(`init book: ${!$(numInCell).has(".fc-selected-date")}`);

				let updateDateFieldIfInForm = function(target, date) {
					let $input = $(target).closest(".calendar_wrapper").siblings("input[name='date']");

					if ( $(target).closest("form").length !== 0 
						|| $(target).closest("[data-filter-wrapper]").length !== 0) {

						if (date !== ""){
							let tmp = date.split("-");
							let correctDate = tmp[2] + "." + tmp[1] + "." + tmp[0];
							$input.attr("value", correctDate);
							$input.val(correctDate);							
						} else {
							$input.attr("value", "");
						}
					}
				};

				if(numInCell.tagName == "SPAN"){

					if( $(numInCell).hasClass("fc-selected-date") ){
						$(numInCell).removeClass("fc-selected-date");
						selectedDate = "";
						updateDateFieldIfInForm(numInCell, selectedDate);
						$button.addClass("_hide");
					} else {
						$(".fc-selected-date").removeClass("fc-selected-date");
						$(numInCell).addClass("fc-selected-date");
						updateDateFieldIfInForm(numInCell, selectedDate);
						$button.removeClass("_hide");
					}

				} else {

					if( $(numInCell).find("span").hasClass("fc-selected-date") ){
						$(numInCell).find("span").removeClass("fc-selected-date");
						selectedDate = "";
						updateDateFieldIfInForm(numInCell, selectedDate);
						$button.addClass("_hide");
					} else {
						$(".fc-selected-date").removeClass("fc-selected-date");
						$(numInCell).find("span").addClass("fc-selected-date");
						updateDateFieldIfInForm(numInCell, selectedDate);
						$button.removeClass("_hide");
					}

				}

				if($(numInCell).closest('form').length > 0){
					$(numInCell).closest(".calendar_container").addClass("_hide");
				}
			}
		});

		calendar.render();
	}

	initFooter(calendarEl){

		if ( $(calendarEl).closest(".form_wrapper").length !== 0 ) {
			let checkbox =
			 `<div class="checkbox_item" data-action="form_checkbox">
					<input type="checkbox" name="restaurant"/>
					<div class="checkbox_pseudo"><span class="calendar_checkbox">Все свободные до 31 декабря</span></div>
				</div>`;
			$(calendarEl).closest(".calendar_container").append(checkbox);

		} else if ( $(calendarEl).closest(".room_card").length !== 0 ) {
			let button = `<button class="fc-booking-button _hide">Забронировать</button>`;

			$(calendarEl).closest(".calendar_container").append(button);

			$(".calendar_container").find(".fc-booking-button").on("click", function(e){
				e.stopImmediatePropagation();

				let transformDate = function() {
					let incorrentDate = $(".fc-selected-date").parent().data("date");
					let correctDate = "";
					let datePattern = /[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/;

					if ( datePattern.test(incorrentDate) ){
						let tmp = incorrentDate.split("-");
						return correctDate = tmp[2] + "." + tmp[1] + "." + tmp[0];
					} else {
						return;
					}	
				}

				let selectedDate = transformDate();
				
				if ( $(e.currentTarget).closest(".form_wrapper").length !== 0 ) {
					$(e.currentTarget).closest("input[name ='date']").attr("value", selectedDate);

				} else if ( $(e.currentTarget).closest(".room_card").length !== 0 ) {
					let hallName = $(e.target).closest(".room_card").children("h2").text();
					let $bookingForm = $(".booking");
					//console.log(`click book: ${$bookingForm.find(".checkbox_pseudo").length}`);

					for ( let checkbox of $bookingForm.find(".checkbox_pseudo") ){
						if ($(checkbox).text() == hallName) {
							//console.log(`зал найден: ${hallName}`);

							let destination = $bookingForm.offset().top;
							$("html").animate({ scrollTop: destination }, 1100);

							$(checkbox).closest(".checkbox_item").addClass("_active");

							//console.log(`зал найден: ${$("input[name = 'date' ]").length}`);
							$bookingForm.find("input[name ='date']").attr("value", selectedDate);
							$bookingForm.find("input[name ='date']").val(selectedDate);							

							break;
						}
					}
				}
				$(e.currentTarget).closest(".calendar_container").addClass("_hide");
			});
		}
	}

	initCalendarButtons(){
		let $buttons = $(".open_calendar_button");

		$buttons.on("click", function(e){
			let $button = $(e.target).closest(".open_calendar_button");
			let $calendar = $button.next();

			if ( !$button.hasClass("_active") ) {
				$button.addClass("_active");
			} else {
				$button.removeClass("_active");
			}

			if ( $calendar.hasClass("_hide") ) {
				$calendar.removeClass("_hide");
			} else {
				$calendar.addClass("_hide");
			}

			e.stopImmediatePropagation();
		});

		$(document).mouseup(function (e){
			let div = $(".calendar_wrapper");

			for ( let cal of div){
				if (!$(cal).is(e.target) 
					&& $(cal).has(e.target).length === 0
					&& !$(cal).children(".calendar_container").hasClass("_hide")) {
					$(cal).children(".calendar_container").addClass ("_hide");
					$(cal).children(".open_calendar_button").removeClass("_active");
				}
			}
		});
	}
}
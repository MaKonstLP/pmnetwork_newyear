"use strict";

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';

export default class CalendarCustom{
  constructor(calendarEl){
		this.init(calendarEl);
  }

  init(calendarEl) {
		let calendar = new Calendar(calendarEl, {
			firstDay: 1,
			locale: ruLocale,
			aspectRatio: 1.35,
			height: 230,
			selectedDate: null,

			customButtons: {
				booking: {
					text: "Забронировать",

					click: function(e){
						let transformDate = function() {
							let correctDate = "";
							let datePattern = /[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/;

							if ( datePattern.test(calendar.selectedDate) ){
								let tmp = calendar.selectedDate.split("-");
								return correctDate = tmp[2] + "." + tmp[1] + "." + tmp[0];
							} else {
								return;
							}	
						}
						let hallName = $(e.target).closest(".room_card").children("h2").text();
						let selectedDate = transformDate();
						let $bookingForm = $(".booking");
						//console.log(`click book: ${$bookingForm.find(".checkbox_pseudo").length}`);

						for ( let checkbox of $bookingForm.find(".checkbox_pseudo") ){
							if ($(checkbox).text() == hallName) {
								//console.log(`зал найден: ${hallName}`);

								let destination = $bookingForm.offset().top;
								$("html").animate({ scrollTop: destination }, 1100);

								$(checkbox).closest(".checkbox_item").addClass("_active");

								//console.log(`зал найден: ${$("input[name = 'date' ]").length}`);
								$("input[name = 'date' ]").attr("value", selectedDate);
								break;
							}
						}
					}
				}
			},

			header: {
				left: "title",
				right: "prev,next"
			},

			footer: {
				center: "booking"
			},

			plugins: [ dayGridPlugin, interactionPlugin ],

			dateClick: function(info){
				let numInCell = info.jsEvent.target;
				this.selectedDate = info.dateStr;
				//console.log(`init book: ${!$(numInCell).has(".fc-selected-date")}`);

				if(numInCell.tagName == "SPAN"){

					if( $(numInCell).hasClass("fc-selected-date") ){
						$(numInCell).removeClass("fc-selected-date");
						this.selectedDate = null;
					} else {
						$(".fc-selected-date").removeClass("fc-selected-date");
						$(numInCell).addClass("fc-selected-date");
					}

				} else {

					if( $(numInCell).find("span").hasClass("fc-selected-date") ){
						$(numInCell).find("span").removeClass("fc-selected-date");
						this.selectedDate = null;
					} else {
						$(".fc-selected-date").removeClass("fc-selected-date");
						$(numInCell).find("span").addClass("fc-selected-date");
					}

				}
			}
		});

    calendar.render();
	}
}
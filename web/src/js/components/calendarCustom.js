"use strict";

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';

export default class CalendarCustom{
  constructor(calendarEl){
		//let self = this;
		this.init(calendarEl);
  }

  init(calendarEl) {
		var calendar = new Calendar(calendarEl, {
			firstDay: 1,
			locale: ruLocale,
			aspectRatio: 1.35,
			height: 230,
			selectedDate: null,

			customButtons: {
				booking: {
					text: "Забронировать",
					click: function(){
						//console.log(`click book: ${calendar.selectedDate}`);
						
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
				//console.log(`init book: ${this.selectedDate}`);
				
				if($(".fc-selected-date").length !== 0){
					$(".fc-selected-date").removeClass("fc-selected-date");
				} 

				if(numInCell.tagName == "SPAN"){
					$(numInCell).addClass("fc-selected-date");
				} else {
					$(numInCell).find("span").addClass("fc-selected-date");
				}
			}
		});

    calendar.render();
	}
}
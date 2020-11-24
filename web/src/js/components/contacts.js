'use strict';

export default class Contacts{
	constructor(){
		$('[data-action="show_phone"]').on("click", function(){
			$(".object_book").addClass("_active");
			$(".object_book_hidden").addClass("_active");
			$(".object_book_interactive_part").removeClass("_hide");
			$(".object_book_send_mail").removeClass("_hide");
			ym(66603799,'reachGoal','showphone');
			dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Search', 'eventAction' : 'ShowPhone'});
		});
	}
}
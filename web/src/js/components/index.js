'use strict';
import Filter from './filter';
import YaMapAll from './map';

export default class Index{
	constructor($block){
		var self = this;
		this.block = $block;
		this.filter = new Filter($('[data-filter-wrapper]'));
		this.yaMap = new YaMapAll(this.filter);

		//КЛИК ПО КНОПКЕ "ПОДОБРАТЬ"
		$('[data-filter-button]').on('click', function(){
			self.redirectToListing();
		});
	}

	redirectToListing(){
		this.filter.filterMainSubmit();
		this.filter.promise.then(
			response => {
				ym(66603799,'reachGoal','filter');
				dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Search', 'eventAction' : 'Filter'});
				window.location.href = response;
			}
		);
	}
}
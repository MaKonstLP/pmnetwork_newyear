import $ from 'jquery';

import Listing from './components/listing';
import Item from './components/item';
import Main from './components/main';
import Index from './components/index';
import Widget from './components/widget';
import Form from './components/form';
import YaMapSingleObject from './components/mapSingleObject';
import YaMapAll from './components/map';
import CalendarCustom from './components/calendarCustom';

window.$ = $;

(function($) {
  	$(function() {

  		if ($('[data-page-type="listing"]').length > 0) {
	    	var listing = new Listing($('[data-page-type="listing"]'));
	    }

	    if ($('[data-page-type="item"]').length > 0) {
				var item = new Item($('[data-page-type="item"]'));
	    }

	    if ($('[data-page-type="index"]').length > 0) {
	    	var index = new Index($('[data-page-type="index"]'));
	    }

	    if ($('[data-widget-wrapper]').length > 0) {
	    	var widget = new Widget();
	    }

	    if ($('.map').length > 0) {
				if($('[data-page-type="item"]').length > 0) {
					var yaMap = new YaMapSingleObject();
				} else {
					var yaMap = new YaMapAll();
	    	}
			}

			if ($('.calendar').length > 0) {
				for(let cal of ($('.calendar'))){
					var calendar = new CalendarCustom(cal);
				}
			}

	    var main = new Main();
	    var form = [];

	    $('form').each(function(){
	    	form.push(new Form($(this)))
	    });

  	});
})($);
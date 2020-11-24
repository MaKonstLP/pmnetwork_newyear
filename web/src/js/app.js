import $ from 'jquery';

import Listing from './components/listing';
import Item from './components/item';
import Main from './components/main';
import Contacts from './components/contacts';
import Index from './components/index';
import Widget from './components/widget';
import Form from './components/form';
import YaMapSingleObject from './components/mapSingleObject';
import CalendarCustom from './components/calendarCustom';
import WidgetMain from './components/widgetMain';
import Breadcrumbs from './components/breadcrumbs';
import Post from './components/post';

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
			
			if ($('[data-widget-main-wrapper]').length > 0) {
	    	var widgetMain = new WidgetMain();
			}

	    if ($('.map').length > 0) {
			if($('[data-page-type="item"]').length > 0) {
				var yaMap = new YaMapSingleObject();
			}
		}

		if ($('.calendar').length > 0) {
			for(let cal of ($('.calendar'))){
				var calendar = new CalendarCustom(cal);
			}
		}

		if ($('.breadcrumbs_slices_container').length > 0) {
				var breadcrumbs = new Breadcrumbs();
		}

		if ($('[data-page-type="contacts"]').length > 0) {
	    	var contacts = new Contacts();
	    }

	    if ($('[data-page-type="post"]').length > 0) {
	    	var post = new Post($('[data-page-type="post"]'));
	    }



	    var main = new Main();
	    var form = [];

	    $('form').each(function(){
	    	form.push(new Form($(this)))
	    });

  	});
})($);

function mapInit(){
	console.log(1);
}
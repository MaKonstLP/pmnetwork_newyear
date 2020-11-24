'use strict';

export default class Breadcrumbs{
	constructor(){
		this.init();
	}

	init() {

    var $container = $('.breadcrumbs_slices_container');

    $container.find('.arrow').on('click', function(e){

      if ($container.find('.breadcrumbs_slices_list').hasClass('_hide')){
        $container.find('.breadcrumbs_slices_list').removeClass('_hide');
        $(this).addClass('_opened');
        
      } else {
        $container.find('.breadcrumbs_slices_list').addClass('_hide');
        $(this).removeClass('_opened');
      }
    });

    $('body').on('click', function(e){

      if (!$(e.target).hasClass('arrow')){
        $container.find('.breadcrumbs_slices_list').addClass('_hide');
        $container.find('.arrow').removeClass('_opened');
      }
    });
		
	}
}
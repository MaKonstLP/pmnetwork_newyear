'use strict';

import Inputmask from 'inputmask';

export default class Filter{
	constructor($filter){
		let self = this;
		this.$filter = $filter;
		this.state = {};

		this.init(this.$filter);

		//КЛИК ПО БЛОКУ С СЕЛЕКТОМ
		this.$filter.find('[data-filter-select-current]').on('click', function(){
			let $parent = $(this).closest('[data-filter-select-block]');
			self.selectBlockClick($parent);	
		});

		//КЛИК ПО СТРОКЕ В СЕЛЕКТЕ
		this.$filter.find('[data-filter-select-item]').on('click', function(){
			$(this).toggleClass('_active');
			self.selectStateRefresh($(this).closest('[data-filter-select-block]'));
		});

		//КЛИК ПО ЧЕКБОКСУ
		this.$filter.find('[data-filter-checkbox-item]').on('click', function(){
			$(this).toggleClass('_checked');
			self.checkboxStateRefresh($(this));
		});

		//КЛИК ВНЕ БЛОКА С СЕЛЕКТОМ
		$('body').click(function(e) {
		    if (!$(e.target).closest('.filter_select_block').length){
		    	self.selectBlockActiveClose();
		    }
		});

		//ИНПУТ
		this.$filter.find('[data-filter-input-block] input').on("keyup", function(event) {
		    var selection = window.getSelection().toString(); 
		    if (selection !== '') {
		        return; 
		    }      
		    if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
		        return; 
		    }       
		    var $this = $(this);
		    var input = $this.val();
		    input = input.replace(/[\D\s\._\-]+/g, ""); 
		    input = input?parseInt(input, 10):0;

		    self.inputStateRefresh($(this).attr('name'), input);
		    $this.val(function () {
		        return (input === 0)?"":input.toLocaleString("ru-RU"); 
		    }); 
		}); 
	}

	init(){
		let self = this;

		this.$filter.find('[data-filter-select-block]').each(function(){
			self.selectStateRefresh($(this));
		});

		this.$filter.find('[data-filter-checkbox-item]').each(function(){
			self.checkboxStateRefresh($(this));
		});
	}

	filterListingSubmit(page = 1){
		let self = this;
		self.state.page = page;

		let data = {
			'filter' : JSON.stringify(self.state)
		}

		this.promise = new Promise(function(resolve, reject) {
			self.reject = reject;
			self.resolve = resolve;
	    });		
		
		$.ajax({
            type: 'get',
            url: '/ajax/filter/',
            data: data,
            success: function(response) {
            	response = $.parseJSON(response);
                self.resolve(response);
            },
            error: function(response) {

            }
        });
	}

	filterMainSubmit(){
		let self = this;
		let data = {
			'filter' : JSON.stringify(self.state)
		}

		this.promise = new Promise(function(resolve, reject) {
			self.reject = reject;
			self.resolve = resolve;
	    });

		$.ajax({
            type: 'get',
            url: '/ajax/filter-main/',
            data: data,
            success: function(response) {
            	if(response){
            		//console.log(response);
            		self.resolve('/ploshhadki/'+response);
            	}
            	else{
            		//console.log(response);
            		self.resolve(self.filterListingHref());
            	}
            },
            error: function(response) {

            }
        });
	}

	selectBlockClick($block){
		if($block.hasClass('_active')){
			this.selectBlockClose($block);
		}
		else{
			this.selectBlockOpen($block);			
		}
	}

	selectBlockClose($block){
		$block.removeClass('_active');
	}

	selectBlockOpen($block){
		this.selectBlockActiveClose();
		$block.addClass('_active');
	}

	selectBlockActiveClose(){
		this.$filter.find('[data-filter-select-block]._active').each(function(){
			$(this).removeClass('_active');
		});
	}

	selectStateRefresh($block){
		let self = this;
		let blockType = $block.data('type');		
		let $items = $block.find('[data-filter-select-item]._active');
		let $currentSelect = $block.find('[data-filter-select-current]');
		let selectText = $currentSelect.data('placeholder');

		if($items.length > 0){
			self.state[blockType] = '';
			$items.each(function(){
				if(self.state[blockType] !== ''){
					self.state[blockType] += ','+$(this).data('value');
					selectText = 'Выбрано ('+$items.length+')';
					$currentSelect.addClass('_disable_placeholder');
				}
				else{
					self.state[blockType] = $(this).data('value');
					selectText = $(this).text();
					$currentSelect.addClass('_disable_placeholder');
				}
			});
		}
		else{
			delete self.state[blockType];
			$currentSelect.removeClass('_disable_placeholder');
		}

		$block.find('[data-filter-select-current] p').text(selectText);
	}

	checkboxStateRefresh($item){
		let blockType = $item.closest('[data-type]').data('type');
		if($item.hasClass('_checked')){
			this.state[blockType] = $item.find('[data-value]').data('value');
		}
		else{
			delete this.state[blockType];
		}
		// console.log(this.state);
	}

	inputStateRefresh(type, val){
		if(val > 0){
			this.state[type] = val;
		}
		else{
			delete this.state[type];
		}
	}

	filterListingHref(){
		if(Object.keys(this.state).length > 0){
			var href = '/ploshhadki/?';
			$.each(this.state, function(key, value){
				href += '&' + key + '=' + value;
			});
		}
		else{
			var href = '/ploshhadki/';
		}			

		return href;
	}
}
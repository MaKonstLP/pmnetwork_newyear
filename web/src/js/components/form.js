import Animation from './animation.js';
//import modal from './modal';
import {status, json} from './utilities';
import Inputmask from 'inputmask';
var animation = new Animation;

export default class Form {
	constructor(form) {
		this.$form = $(form);
		this.$formWrap = this.$form.parents('.form_wrapper');
		this.$submitButton = this.$form.find('button[type="submit"]');
		this.$policy = this.$form.find('[name="policy"]');
		this.to = (this.$form.attr('action') == undefined || this.$form.attr('action') == '') ? this.to : this.$form.attr('action');
		let im_phone = new Inputmask('+7 (999) 999-99-99', {
			clearIncomplete: true,
	    });
	    im_phone.mask($(this.$form).find('[name="phone"]'));

		this.bind();
	}

	bind() {

		this.$form.find('[data-dynamic-placeholder]').each(function () {
			$(this).on('blur',function () {
				if ($(this).val() == '')
					$(this).removeClass('form_input_filled');
				else
					$(this).addClass('form_input_filled');
			})
		})

		this.$form.find('[data-required]').each((i, el) => {
			$(el).on('blur', (e) => {
				this.checkField($(e.currentTarget));
				this.checkValid();
			});

			$(el).on('change', (e) => {
			  // console.log('input change');
			  this.checkValid();
			  // this.checkField($(e.currentTarget));
			  // this.checkValid();
			});
		});

		this.$form.on('submit', (e) => {
			this.sendIfValid(e);
		});

		this.$form.on('click', 'button.disabled', function(e) {
			e.preventDefault();
			return false;
		});

		this.$policy.on('click',(e) => {
			var $el = $(e.currentTarget);

			if ($el.prop('checked'))
			$el.removeClass('_invalid');
				else
			$el.addClass('_invalid');

			this.checkValid();
		});

		$('[data-action="form_checkbox"]').on('click',(e) => {
			let $el = $(e.currentTarget);
			let $input = $el.siblings('input');
			console.log(1);

			if(!$(e.target).hasClass('_link')){
				console.log(2);
				$el.toggleClass("_active");
				$input.prop("checked", !$input.prop("checked"));
				e.stopImmediatePropagation();
			}

				
		});

		this.$formWrap.find('[data-success] [data-success-close]').on('click', (e) => {
			this.$formWrap.find('[data-success]').addClass('_hide');
		});

		/*this.$form.find('[data-form-privacy]').on('click', (e) => {
			let $el = $(e.currentTarget);
			console.log(1);

			if(!$(e.target).hasClass('_link')){
				console.log(2);
				$el.toggleClass('_active');

				if($el.hasClass('_active')){
					this.$submitButton.removeClass('disabled');
				}
				else{
					this.$submitButton.addClass('disabled');
				}
			}
		});*/

	}

	checkValid() {
		this.$submitButton.removeClass('disabled');
		if (this.$form.find('.form_input_invalid').length > 0) {
			this.$submitButton.addClass('disabled');
		}
	}

	checkField($field) {
			var valid = true;
			var name = $field.attr('name');
			var pattern_email = /^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i;

			if ($field.val() == '') {
				valid = false;
			} else {
				if (name === 'phone' && $field.val().indexOf('_') >= 0) {
					valid = false;
					var custom_error = 'Неверный формат телефона';
				}

		    if (name === 'email' && !(pattern_email.test($field.val()))) {
					valid = false;
					var custom_error = 'Неверный формат электронной почты';
				}

		    if (name === 'policy' && $field.prop('checked'))
		      valid = true;
			}
			if (valid) {
				$field.removeClass('_invalid');

        if ($field.parent().find('.form_input_error').length > 0)
					$field.parent().find('.form_input_error').html('');

			} else {
				$field.addClass('_invalid');
				var form_error = $field.data('error') || 'Заполните поле';
				var error_message = custom_error || form_error;

				if ($field.siblings('.form_input_error').length  == 0) {
					$field.parent('.elementWrap').append('<div class="form_input_error">' + error_message + '</div>');
				} else {
					$field.siblings('.form_input_error').html(error_message);
				}
			}
	}

	checkFields() {
		var valid = true;

    	this.$form.find('[data-required]').each((i, el) => {
			this.checkField($(el));
			if ($(el).hasClass('_invalid'))
				valid = false;
		});

		if (valid) {
			this.$submitButton.removeClass('disabled');
		} else {
			this.$form.find('._invalid')[0].focus();
			this.$submitButton.addClass('disabled');
		}

		return valid;
	}

	reset() {
		this.$form[0].reset();
		this.$form.find('input').removeClass('form_input_valid form_input_filled');
	}

	beforeSend() {
		this.$submitButton.addClass('button__pending');
	}

	success(data, formType) {
		//modal.append(data);
		//modal.show();
		switch(formType) {
		  case 'main':
		    ym(66603799,'reachGoal','feedback');
		    dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Order', 'eventAction' : 'Feedback'});
				gtag('event', 'feedback', {'event_category' : 'Order', 'event_action' : 'Feedback'});
		    break;
		  case 'item':
		    ym(66603799,'reachGoal','roomorder');
		    dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Order', 'eventAction' : 'Roomorder'});
				gtag('event', 'roomorder', {'event_category' : 'Order', 'event_action' : 'Roomorder'});
		    break;
		  case 'header':
		    ym(66603799,'reachGoal','quickorder');
		    dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Order', 'eventAction' : 'Quickorder'});
				gtag('event', 'quickorder', {'event_category' : 'Order', 'event_action' : 'Quickorder'});
		    break;
			case 'book':
				ym(66603799,'reachGoal','roominfo');
				dataLayer.push({'event': 'event-to-ga', 'eventCategory' : 'Search', 'eventAction' : 'Roominfo'});
				gtag('event', 'roominfo', {'event_category' : 'Order', 'event_action' : 'Roominfo'});
				$('.object_book_email._form').removeClass('_form').addClass('_success');
				break;
		}
		this.$submitButton.removeClass('button__pending');
		this.reset();
		this.$formWrap.find('[data-success] [data-success-name]').text(data.payload.name);
		this.$formWrap.find('[data-success] [data-success-phone]').text(data.payload.phone);
		this.$formWrap.find('[data-success]').removeClass('_hide');
	}

	error() {
		// this.$submitButton.removeClass('button__pending');
		//modal.showError();
	}

	sendIfValid(e) {
	    e.preventDefault();
	    if (!this.checkFields()) return;
	    if (this.disabled) return;

	    this.disabled = true;
	    this.beforeSend();

	    var formData = new FormData(this.$form[0]);

	    var formType = this.$form.data('type');
	    formData.append('type', formType);
	    var formUrl = window.location.href;
	    formData.append('url', formUrl);
	    var cityID = $('[data-city-id]').data('city-id');
	    formData.append('cityID', cityID);

	    for (var pair of formData.entries()) {
		    console.log(pair[0]+ ', ' + pair[1]);
		}

	    fetch(this.to,{
			method: 'POST',
			body: formData
	    })
	    .then(status)
	    .then(json)
	    .then(data => {
			this.success(data, formType);
			// this.reset();
			this.disabled = false;
	    })
	    .catch(() => {
			this.error();
			this.disabled = false;
	    });
	}
}

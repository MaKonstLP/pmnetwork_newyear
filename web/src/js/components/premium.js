'use strict';
import Cookies from 'js-cookie';

export default class Premium{
	constructor(){
		if (!Cookies.get('premium_unique')){
			let data = new FormData();
			data.append('gorko_id', $('[data-premium-rest]').data('premium-rest'));
			data.append('channel', $('[data-channel-id]').data('channel-id'));
			fetch('/premium/premium-unique/', {
				method: 'POST',
				body: data,
			})
				.then((response) => response.json())
				.then((data) => {
					Cookies.set('premium_unique', 1, { expires: 30, path: ''});
					console.log(data);
				})
				.catch((error) => {
					console.error('Error:', error);
				});
		}
	}
}
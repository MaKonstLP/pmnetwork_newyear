"use strict";

export default class YaMapAll{
	constructor(){
		this.init();
	}

	init() {
		ymaps.ready(function(){
			let map = document.querySelector(".map");
			let myMap = new ymaps.Map(map, {center: [55.76, 37.64], zoom: 15});

			let myBalloonLayout = ymaps.templateLayoutFactory.createClass(
				`<div class="balloon_layout">
					<a class="close" href="#"></a>
					<div class="arrow"></div>
					<div class="balloon_inner">
						$[[options.contentLayout]]
					</div>
				</div>`, {
				build: function() {
					this.constructor.superclass.build.call(this);

					this._$element = $('.balloon_layout', this.getParentElement());

					this._$element.find('.close')
                        .on('click', $.proxy(this.onCloseClick, this));

				},

				clear: function () {
					this._$element.find('.close')
							.off('click');

					this.constructor.superclass.clear.call(this);
				},

				onCloseClick: function (e) {
					e.preventDefault();

					this.events.fire('userclose');
				},

				getShape: function () {
					if(!this._isElement(this._$element)) {
							return myBalloonLayout.superclass.getShape.call(this);
					}

					var position = this._$element.position();

					return new ymaps.shape.Rectangle(new ymaps.geometry.pixel.Rectangle([
							[position.left, position.top], [
									position.left + this._$element[0].offsetWidth,
									position.top + this._$element[0].offsetHeight + this._$element.find('.arrow')[0].offsetHeight
							]
					]));
				},

				_isElement: function (element) {
					return element && element[0] && element.find('.arrow')[0];
				}
				}
			);

			let myBalloonContentLayout = ymaps.templateLayoutFactory.createClass(
				`<div class="balloon_wrapper">

					<div class="balloon_content">

						<img src={{properties.img}}>

						<div class="balloon_text">

							<div class="balloon_header">
								{{properties.organization}}
							</div>

							<div class="balloon_address">
								{{properties.address}}
							</div>

						</div>

					</div>

					<div class="balloon_link">
						<button class="balloon_link_button _button"><a href="#">Посмотреть зал</a></button>
					</div>
					
				</div>`
			);

			let objectManager = new ymaps.ObjectManager(
				{
					geoObjectBalloonLayout: myBalloonLayout, 
					geoObjectBalloonContentLayout: myBalloonContentLayout,
					geoObjectHideIconOnBalloonOpen: false,
					geoObjectBalloonOffset: [-360, 17],
					clusterize: true,
					clusterDisableClickZoom: false,
					clusterBalloonItemContentLayout: myBalloonContentLayout,
					clusterIconColor: "green",
					geoObjectIconColor: "green"
				}
			);

			let serverData = null;
			let data = {subdomain_id : $('[data-map-api-subid]').data('map-api-subid')};
			$.ajax({
	            type: "POST",
	            url: "/api/map_all/",
	            data: data,
	            success: function(response) {
	            	serverData = response;
					
					objectManager.add(serverData);  
					//console.log(`objectManager length: ${objectManager.objects.getLength()}`);
					myMap.geoObjects.add(objectManager);
					//console.log(`objectManager: ${objectManager.getBounds()}`);
					myMap.setBounds(objectManager.getBounds());
	            },
	            error: function(response) {

	            }
	        });
			/*let serverResponse = fetch("/api/map_all/", {
				    method: 'post',
				    mode:    'cors',
				    headers: {
				      'Content-Type': 'application/json',  // sent request
				      'Accept':       'application/json'   // expected data sent back
				    },
				    body: JSON.stringify(data),
				})
				.then(function(response) {
					if (response.ok) { 
						let json = response.json();
						return json;
					} else {
						alert("Ошибка HTTP: " + response.status);
					}
				})
				.then(function(json) {
					serverData = json;
					
					objectManager.add(serverData);  
					//console.log(`objectManager length: ${objectManager.objects.getLength()}`);
					myMap.geoObjects.add(objectManager);
					//console.log(`objectManager: ${objectManager.getBounds()}`);
					myMap.setBounds(objectManager.getBounds());
				});*/				
		});
	}
}
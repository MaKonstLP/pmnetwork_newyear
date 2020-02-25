'use strict';

export default class YaMap{
	constructor(){
		/*ymaps.ready(function () {
	    var myMap = new ymaps.Map('map', {
	        center: [
            	$('.map #map').data('mapdotx'),
            	$('.map #map').data('mapdoty'),
            ],
            zoom: 15
        }),

        myPlacemark = new ymaps.Placemark(myMap.getCenter(), {
            hintContent: 'hintContent',
            balloonContent: 'balloonContent'
        }, {
            iconLayout: 'default#image',
        });

	    myMap.geoObjects
		        .add(myPlacemark); 
		});*/

		ymaps.ready(init);
		function init() {
			let map = document.querySelector(".map");
			let myMap = new ymaps.Map(map, {center: [55.76, 37.64], zoom: 7});

			let myGeoObject = new ymaps.GeoObject({
				geometry: {
						type: "Point",
						coordinates: [55.8, 37.8],
				},
				properties: {
					balloonContent: "Test point"
				}
			});

			myGeoObject.events.add("click", function(e){
				let obj = e.get("target");
				alert(e.get("coordinates"));
				//obj.Balloon.open;
			});
		
			myMap.geoObjects.add(myGeoObject);

			/*let objectManager = new ymaps.ObjectManager();

			$.getJSON("http://newyearpmn_artem.ru/api/map_all/", function (json) {
			objectManager.add(json);  
			
			alert(objectManager.getLength());
});*/

		}
	}
}
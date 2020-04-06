"use strict";

export default class YaMapSingleObject{
	constructor(){
		this.init();
	}

	init() {
		ymaps.ready(function(){
			let map = document.querySelector(".map");
      let myMap = new ymaps.Map(map, {center: [55.76, 37.64], zoom: 15, controls: []},
                  {suppressMapOpenBlock: true});

      let zoomControl = new ymaps.control.ZoomControl({
        options: {
            size: "small",
            position: {
              top: 10,
              right: 10
            }

        }
      });

      let geolocationControl = new ymaps.control.GeolocationControl({
        options: {
          noPlacemark: true,
          position: {
            top: 10,
            left: 10
          }
        }
    });

      myMap.controls.add(zoomControl);
      myMap.controls.add(geolocationControl);

      let objectCoordinates = [$("#map").attr("data-mapDotX"), $("#map").attr("data-mapDotY")];
      let myBalloonHeader = $("#map").attr("data-name");
      let myBalloonBody = $("#map").attr("data-address");
      let myBalloonLayout = ymaps.templateLayoutFactory.createClass(
				`<div class="balloon_layout _single_object">
					<div class="arrow"></div>
          <div class="balloon_inner">
            <div class="balloon_inner_header">
              {{properties.balloonContentHeader}}
            </div>
            <div class="balloon_inner_body">
              {{properties.balloonContentBody}}
            </div>
					</div>
				</div>`
      );

      let object = new ymaps.Placemark(objectCoordinates, {
        balloonContentHeader: myBalloonHeader,
        balloonContentBody: myBalloonBody
      }, {
        iconColor: "green",
        balloonLayout: myBalloonLayout,
        hideIconOnBalloonOpen: false,
        balloonOffset: [-150, 17],
      });

      myMap.geoObjects.add(object);
      myMap.setCenter(objectCoordinates);
      object.balloon.open( "", "", {closeButton: false});

		});
	}
}
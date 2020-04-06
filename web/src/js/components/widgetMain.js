"use strict";

import Swiper from 'swiper';

export default class WidgetMain{
  constructor(){
    this.init();
  }

  init(){
    var swiper = new Swiper('.swiper-container', {
      slidesPerView: "auto",
      //spaceBetween: 30,
      watchOverflow: true,
      slidesPerGroup: 1,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
      },
    });
  }
}
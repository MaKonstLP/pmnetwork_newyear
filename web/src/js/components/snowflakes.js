'use strict';
export default class Snowflakes {
	constructor() {
		let self = this;
		var fired = false;

		this.snowmax = 35;
		// Set the colors for the snow. Add as many colors as you like
		this.snowcolor = new Array("#b9dff5", "#b9dff5", "#b9dff5", "#b9dff5", "#b9dff5");
		// Set the fonts, that create the snowflakes. Add as many fonts as you like
		this.snowtype = new Array("Times");
		// Set the letter that creates your snowflake (recommended: * )
		this.snowletter = "*";
		// Set the speed of sinking (recommended values range from 0.3 to 2)
		this.sinkspeed = 0.6;
		// Set the maximum-size of your snowflakes
		this.snowmaxsize = 35;
		// Set the minimal-size of your snowflakes
		this.snowminsize = 8;
		// Set the snowing-zone
		// Set 1 for all-over-snowing, set 2 for left-side-snowing
		// Set 3 for center-snowing, set 4 for right-side-snowing
		this.snowingzone = 1;
		///////////////////////////////////////////////////////////////////////////
		// CONFIGURATION ENDS HERE
		///////////////////////////////////////////////////////////////////////////

		// Do not edit below this line
		this.snow = new Array();
		this.marginbottom;
		this.marginright;
		this.timer;
		this.i_snow = 0;
		this.x_mv = new Array();
		this.crds = new Array();
		this.lftrght = new Array();
		this.browserinfos = navigator.userAgent;
		this.ie5 = document.all && document.getElementById && !this.browserinfos.match(/Opera/);
		this.ns6 = document.getElementById && !document.all;
		this.opera = this.browserinfos.match(/Opera/);
		this.browserok = this.ie5 || this.ns6 || this.opera;

		window.addEventListener('click', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		window.addEventListener('scroll', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		window.addEventListener('mousemove', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		window.addEventListener('touchmove', () => {
			if (fired === false) {
				fired = true;
				load_other();
			}
		}, { passive: true });

		function load_other() {
			setTimeout(function () {
				self.init();
			}, 100);
		}
	}

	init() {
		for (let i = 0; i <= this.snowmax; i++) {
			const span = document.createElement('span');
			span.id = 's' + i;
			span.style.position = 'absolute';
			span.style.top = '-'+this.snowmaxsize+'px';
			span.innerText = this.snowletter;
			document.body.appendChild(span);
		}

		if (this.browserok) {
			window.onload = this.initsnow();
		}
	}

	randommaker(range) {
		let rand = Math.floor(range * Math.random())
		return rand;
	}

	initsnow() {
		if (this.ie5 || this.opera) {
			this.marginbottom = document.body.scrollHeight
			this.marginright = document.body.clientWidth - 15
		}
		else if (this.ns6) {
			this.marginbottom = document.body.scrollHeight
			this.marginright = window.innerWidth - 15
		}
		var snowsizerange = this.snowmaxsize - this.snowminsize
		for (let i = 0; i <= this.snowmax; i++) {
			this.crds[i] = 0;
			this.lftrght[i] = Math.random() * 15;
			this.x_mv[i] = 0.03 + Math.random() / 10;
			this.snow[i] = document.getElementById("s" + i);
			this.snow[i].style.fontFamily = this.snowtype[this.randommaker(this.snowtype.length)];
			this.snow[i].size = this.randommaker(snowsizerange) + this.snowminsize;
			this.snow[i].style.fontSize = this.snow[i].size + 'px';
			this.snow[i].style.color = this.snowcolor[this.randommaker(this.snowcolor.length)];
			this.snow[i].style.zIndex = 1000;
			this.snow[i].sink = this.sinkspeed * this.snow[i].size / 5;
			if (this.snowingzone == 1) { this.snow[i].posx = this.randommaker(this.marginright - this.snow[i].size) };
			if (this.snowingzone == 2) { this.snow[i].posx = this.randommaker(this.marginright / 2 - this.snow[i].size) };
			if (this.snowingzone == 3) { this.snow[i].posx = this.randommaker(this.marginright / 2 - this.snow[i].size) + this.marginright / 4 };
			if (this.snowingzone == 4) { this.snow[i].posx = this.randommaker(this.marginright / 2 - this.snow[i].size) + this.marginright / 2 };
			this.snow[i].posy = this.randommaker(2 * this.marginbottom - this.marginbottom - 2 * this.snow[i].size);
			this.snow[i].style.left = this.snow[i].posx + 'px';
			this.snow[i].style.top = this.snow[i].posy + 'px';
		}
		this.movesnow();
	}

	movesnow() {
		for (let i = 0; i <= this.snowmax; i++) {
			this.crds[i] += this.x_mv[i];
			this.snow[i].posy += this.snow[i].sink
			this.snow[i].style.left = this.snow[i].posx + this.lftrght[i] * Math.sin(this.crds[i]) + 'px';
			this.snow[i].style.top = this.snow[i].posy + 'px';

			if (this.snow[i].posy >= this.marginbottom - 2 * this.snow[i].size || parseInt(this.snow[i].style.left) > (this.marginright - 3 * this.lftrght[i])) {
				if (this.snowingzone == 1) { this.snow[i].posx = this.randommaker(this.marginright - this.snow[i].size) }
				if (this.snowingzone == 2) { this.snow[i].posx = this.randommaker(this.marginright / 2 - this.snow[i].size) }
				if (this.snowingzone == 3) { this.snow[i].posx = this.randommaker(this.marginright / 2 - this.snow[i].size) + this.marginright / 4 }
				if (this.snowingzone == 4) { this.snow[i].posx = this.randommaker(this.marginright / 2 - this.snow[i].size) + this.marginright / 2 }
				this.snow[i].posy = 0
			}
		}
		setTimeout(() => this.movesnow(), 50);
	}
}
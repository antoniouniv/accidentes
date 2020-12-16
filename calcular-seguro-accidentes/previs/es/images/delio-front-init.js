function colapsaMobile(elem){
	var expand = true;
	var elemento = angular.element($(elem));
	var isolateScope = elemento.isolateScope();

	if ($(window).width() < 960) {
		expand = false;
	} else {
		expand = true;
	}

	isolateScope.showContent = expand;
	isolateScope.$apply();
}


function reset() {
	$('.mfc-price-box__section .mfc-price-box__button-list:last-child').removeClass('hidden');
	$('.alien-button-list').remove();
}
function moveButton() {
	var linkOcultar = $('.mfc-price-box__section .mfc-price-box__button-list:last-child');
	var link = linkOcultar.clone();
	linkOcultar.addClass('hidden');
	link.addClass('alien-button-list');
	$('.mfc-price-model-mapfre__container .mfc-folding-box').prepend(link);

    link.on('click', function(e){
        e.preventDefault();
        angular.element('.mfc-price-box__section .mfc-price-box__button-list:last-child a').triggerHandler('click');
    });
}


function controlMoveButton() {
	if ($(window).width() >= 960) {
		moveButton();
	} else {
		if($('.alien-button-list').length > 0) {
			reset();
		}
	}
}


$(window).on('hashchange', function(){
	var url = window.location.hash;
	if(url.indexOf('precio') > -1) {
		colapsaMobile('mfc-coverage-info');
		controlMoveButton();

		$(window).resize(function() {
			controlMoveButton();
			colapsaMobile('mfc-coverage-info');
		});
	}
});

/*** Fecron REST Start ***/
//document.addEventListener('DOMContentLoaded', function() {
//if(window.location.hash.indexOf("/fuera-servicio") != -1){
//    var xhttp = new XMLHttpRequest();
//    xhttp.open("GET", "/calcular-seguro-vida/es/api/pages/fueraServicio", true);
//    xhttp.setRequestHeader("Content-type", "application/json");
//    xhttp.send();
//    }
//});
/*** Fecron REST End ***/

/*** Fecron walmerick Start ***/
function ringpoolHeader(){
	var divRingPoolHeader;
	console.debug("::Init walmeric::");
	var intervalID = setInterval(function(){
		divRingPoolHeader = $(".mfc-header .DCSS-c2c");
		divRingPoolHeader.attr("id", "walmeric");
		
		if($(".mfc-header .DCSS-c2c#walmeric").length > 0){
			console.debug("::Init walmeric added class::");
			clearInterval(intervalID);
		}
	}, 200);
}
window.onload = ringpoolHeader;
/*** Fecron walmerick End ***/
$(window).on('load hashchange', function(){
	var url = window.location.hash;

if ( (url.indexOf('datos-contacto') > -1 ) ) {
		setTimeout(function() {
			if( $('.error-scroll').length > 0 ) {
				var disparador = $('.mfc-link-button__green');
				disparador.on('click', function() {
					var elemento = angular.element('.error-scroll');
					var scope = elemento.scope();
					scope.$watch('status', function(){
						if ( scope.status === 'error' ) {
								$('html').animate({
									scrollTop: $(".error-scroll").offset().top
								}, 750);
							$('.error-scroll input').focus();
						}
					});
				});
			}
		}, 2000); 
	} else if ( (url.indexOf('Landing') > -1 ) ) {
		setTimeout(function() {
			if( $('.error-scroll').length > 0 ) {
				var disparador = $('.mfc-link-button__green');
				disparador.on('click', function() {
					var elemento = angular.element('.error-scroll');
					var scope = elemento.scope();
					scope.$watch('status', function(){
						if ( scope.status === 'error' ) {
								$('html').animate({
									scrollTop: $(".error-scroll").offset().top
								}, 750);
							$('.error-scroll input').focus();
						}
					});
				});
			}
		}, 2000); 
	}
});

(function (ng) {
  'use strict';

  ng
    .module('urlAceptacionPoliticas', ['mfc'])
    .run(runBlock);
	
	runBlock.$inject = ['$log', '$rootScope', 'mfcFlowData','mfcGlobalData','$timeout', '$interval'];

  /** @ngInject */
  function runBlock($log, $rootScope, mfcFlowData,mfcGlobalData,$timeout, $interval) {
    

	function stateChangeSuccess(event, toState, toParams, fromState, fromParams) {
		var azul = mfcFlowData;
		var verde = mfcGlobalData;
		var urlName = "";
		var originalUpdateData = mfcFlowData.updateData;
		
		if (toState.name == "datos-contacto") {
			var stopInterval = $interval(function() {
				var link = angular.element("a[title*='rivacidad']");
				if(link.length > 0){
					for (var i = 0; i < link.length; i++) {
						 if(link[i].href){
						 	if(link[i].href.indexOf("privac") >= 0){
						 		urlName = link[i].href;
						 	}
						 }
					}

				}
				 //urlName = getUrlCheck("aceptacionCondiciones");
				 if(urlName != undefined && urlName != ""){
					function extendedUpdateData(state, mfcForm) {
					mfcForm["urlAceptacionPoliticas"] = {
						"$modelValue": urlName,
						"$error": "noparameter"
						};
					originalUpdateData.apply(mfcFlowData, arguments);
					}
					mfcFlowData.updateData = extendedUpdateData;
					$interval.cancel(stopInterval);
				}
			}, 400);	
		}else if(toState.name == "Landing"){
			var stopInterval = $interval(function() {
				 urlName = getUrlCheck("checkBoxPoliticaDePrivacidadCookiesTarificacion");
				 if(urlName != ""){
					function extendedUpdateData(state, mfcForm) {
					  mfcForm["urlAceptacionPoliticas"] = {
						  "$modelValue": urlName,
						"$error": "noparameter"
						};
					  originalUpdateData.apply(mfcFlowData, arguments);
					}
					mfcFlowData.updateData = extendedUpdateData;
					$interval.cancel(stopInterval);
				 }
				 
			}, 400);
		}

		
    }
	
	function getUrlCheck(id){
		var href="";
		var labelCheckBoxUrl = angular.element('label[for="' + id + '"]');
		if(labelCheckBoxUrl){
			var nodes = labelCheckBoxUrl[0].childNodes;
			for (var i = 0; i < nodes.length; i++) {
				if(nodes[i].firstChild !== null){
					if(nodes[i].getAttribute("title")){
						var elementTitle = nodes[i].getAttribute("title");
						if(elementTitle.indexOf("rivacidad") >= 0){
							href = nodes[i].getAttribute("href");

						} 
					}
				}

			}
		}
		return href;		
	}
		
	$rootScope.$on('$stateChangeSuccess', stateChangeSuccess);
  }

  //Add our module to COME
  ng
    .module('come')
    .requires.push('urlAceptacionPoliticas');
})(angular);
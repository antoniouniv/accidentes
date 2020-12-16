(function () {
  'use strict';

  routerConfig.$inject = ["$stateProvider", "$urlRouterProvider"];
  angular
    .module('come')
    .config(routerConfig);

  /** @ngInject */
  function routerConfig($stateProvider, $urlRouterProvider) {
    var defaultHomePage = "fecha-nacimiento";
    var defaultErrorPage = "error";
    $stateProvider
      .state('parent', {
        abstract: true,
        resolve: {
          recoveryConfig: function ($q, $location, mfcGlobalData, mfcFlowData) {

            var path = $location.path();
            var isIsolated = (path.search("isolated") > -1) ? true : false;

            mfcFlowData.defaultHomePage = defaultHomePage;
            mfcFlowData.defaultErrorPage = defaultErrorPage;

            return mfcGlobalData.recoveryConfigPromise(isIsolated);
          }
        }
      })
      
      .state('quote', {
        parent: 'parent',
        abstract: true,
        url: '/',
        views: {
		  '@': {
          	templateUrl: 'app/pages/layout.3-vertical-sections-v3.0.0.html'
          },
          'header@quote': {
            templateUrl: 'app/pages/headerQuote-v1.0.0.html'
          },
          'content@quote': {
            templateUrl: ''
          },
          'footer@quote': {
            templateUrl: 'app/pages/footerQuote-v3.0.0.html'
          }
        }
      })

      .state('isolated', {
        parent: 'quote',
        url: 'isolated/:nameState'
      })
      
      .state('fecha-nacimiento', {
        parent: 'quote',
        url: 'fecha-nacimiento',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/fecha-nacimiento.html'
          }
        }
      })

      .state('profesion', {
        parent: 'quote',
        url: 'profesion',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/profesion.html'
          }
        }
      })

      .state('incluir-garantias', {
        parent: 'quote',
        url: 'incluir-garantias',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/incluir-garantias.html'
          }
        }
      })
	  
	  .state('residencia-habitual-espana', {
        parent: 'quote',
        url: 'residencia-habitual-espana',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/residencia-habitual-espana.html'
          }
        }
      })
	  
	   .state('cliente-mapfre', {
        parent: 'quote',
        url: 'cliente-mapfre',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/cliente-mapfre.html'
          }
        }
      })

	  .state('documento-identidad', {
        parent: 'quote',
        url: 'documento-identidad',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/documento-identidad.html'
          }
        }
      })

      .state('capital', {
        parent: 'quote',
        url: 'capital',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/capital.html'
          }
        }
      })

      .state('Landing', {
        parent: 'quote',
        url: 'Landing',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/Landing.html'
          }
        }
      })

      .state('datos-de-contacto', {
        parent: 'quote',
        url: 'datos-de-contacto',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/datos-de-contacto.html'
          }
        }
      })

      .state('precio', {
        parent: 'quote',
        url: 'precio',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/precio.html'
          }
        }
      })

      .state('modificar-datos', {
        parent: 'quote',
        url: 'modificar-datos',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/modificar-datos.html'
          }
        }
      })

      .state('tarificacion-sin-precio', {
        parent: 'quote',
        url: 'tarificacion-sin-precio',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/tarificacion-sin-precio.html'
          }
        }
      })

      .state('tarificacion-sin-servicio', {
        parent: 'quote',
        url: 'tarificacion-sin-servicio',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/tarificacion-sin-servicio.html'
          }
        }
      })

      .state('fuera-servicio', {
        parent: 'quote',
        url: 'fuera-servicio',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/fuera-servicio.html'
          }
        }
      })

      .state('politica-de-privacidad', {
        parent: 'quote',
        url: 'politica-de-privacidad',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/politica-de-privacidad.html'
          }
        }
      })

      .state('solicitud-exito', {
        parent: 'quote',
        url: 'solicitud-exito',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/solicitud-exito.html'
          }
        }
      })

      .state('error-precarga-datos', {
        parent: 'quote',
        url: 'error-precarga-datos',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/error-precarga-datos.html'
          }
        }
      })

      .state('datos-asegurado', {
        parent: 'quote',
        url: 'datos-asegurado',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/datos-asegurado.html'
          }
        }
      })

      .state('servicio-no-disponible', {
        parent: 'quote',
        url: 'servicio-no-disponible',
        params: {
          focusField: {
            value: null,
            squash: false
          }
        },
        views: {
          'content@quote': {
            templateUrl: 'app/pages/servicio-no-disponible.html'
          }
        }
      })

    $urlRouterProvider.otherwise('/' + defaultHomePage);
  }

})();
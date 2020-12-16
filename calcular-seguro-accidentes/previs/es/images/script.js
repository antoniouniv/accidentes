var myApp = angular.module('come');
myApp.config(['$compileProvider', function ($compileProvider) {
  $compileProvider.debugInfoEnabled(true);
}]);  

$(function() {

    $injector = angular.element("html").injector();
    $rootScope = $injector.get("$rootScope");
    $timeout = $injector.get("$timeout");
    $state = $injector.get('$state');
    mfcFlowData = $injector.get("mfcFlowData");

    
    $rootScope.$on('$viewContentLoaded', function(event, viewName, viewContent) {
        if (viewName == "content@quote") {
            var stateName = $state.$current.self.name;
            switch (stateName) {
                case "solicitud-exito":
                    $timeout(function() { solicitudExito(); }, 0);
                    break;
            }
        }
    });
});  
   
function solicitudExito() {

    $(".mfc-riched-content.mfc-u-riched-content").html($(".mfc-riched-content.mfc-u-riched-content").html().replace('$[0]',mfcFlowData.inTransition.fecha));
    $(".mfc-riched-content.mfc-u-riched-content").html($(".mfc-riched-content.mfc-u-riched-content").html().replace('$[1]',mfcFlowData.inTransition.hora));
}
!function(e){"use strict";function i(e,i){i.initialize(),e.info("walmeric module LOADED.")}i.$inject=["$log","walmericFlowDataExtender"],e.module("walmeric",["mfc"]).run(i),e.module("come").requires.push("walmeric")}(angular);
!function(a){"use strict";function e(e,r){function t(a,t){var l=e.Walmeric_leadid;l&&(t.Walmeric_leadid={$modelValue:l,$error:"noparameter"}),i.apply(r,arguments)}var i=r.updateData;return r.updateData=t,{initialize:a.noop}}e.$inject=["$window","mfcFlowData"],a.module("walmeric").factory("walmericFlowDataExtender",e)}(angular);
//# sourceMappingURL=all.js.map

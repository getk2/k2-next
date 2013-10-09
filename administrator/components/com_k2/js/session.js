define(['dispatcher'], function(K2Dispatcher) {'use strict';
	var K2Session = {
		storage : sessionStorage,
		get : function(key, defaultValue) {
			defaultValue = (defaultValue === undefined) ? '' : defaultValue;
			return (this.storage.getItem(key) === undefined) ? defaultValue : this.storage.getItem(key);
		},
		set : function(key, value) {
			return this.storage.setItem(key, value);
		}
	};
	return K2Session;
});

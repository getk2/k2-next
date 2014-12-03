define(['dispatcher'], function(K2Dispatcher) {'use strict';
	var K2Session = {
		storage : sessionStorage,
		prefix : 'k2',
		get : function(key, defaultValue) {
			defaultValue = (defaultValue === undefined) ? '' : defaultValue;
			return (this.storage.getItem(this.prefix + '.' + key) === null) ? defaultValue : this.storage.getItem(this.prefix + '.' + key);
		},
		set : function(key, value) {
			return this.storage.setItem(this.prefix + '.' + key, value);
		}
	};
	return K2Session;
});

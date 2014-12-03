// Backbone.sync
// -------------

// Override of the default Backbone.sync implementation.
// Enforces Backbone.emulateHTTP = true and Backbone.emulateJSON = true.
// Copies any model attributes to the data object.

Backbone.sync = function(method, model, options) {

	// Initialize the options object if it is not set
	options || ( options = {});
	if (options.data === undefined) {
		options.data = [];
	}

	// Detect the request type
	switch (method) {
		case 'create':
			var type = 'POST';
			break;
		case 'update':
			var type = 'PUT';
			break;
		case 'patch':
			var type = 'PATCH';
			break;
		case 'delete':
			var type = 'DELETE';
			break;
		case 'read':
			var type = 'GET';
			break;
	}

	// Request params
	var params = {
		type : (method === 'read') ? 'GET' : 'POST',
		dataType : 'json',
		contentType : 'application/x-www-form-urlencoded',
		url : _.result(model, 'url') || urlError()
	};

	// Convert any model attributes to data
	_.each(options.attrs, function(value, attribute) {
		options.data.push({
			name : 'states[' + attribute + ']',
			value : value
		});
	});

	// For create, update, patch and delete methods pass as aerguments the method and the session token.
	if (method !== 'read') {
		options.data.push({
			name : '_method',
			value : type
		});
		options.data.push({
			name : K2SessionToken,
			value : 1
		});
	}

	// Make the request, allowing the user to override any Ajax options
	var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
	model.trigger('request', model, xhr, options);
	return xhr;

}; 
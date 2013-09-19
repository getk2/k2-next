'use strict';
define(['marionette', 'router', 'controller', 'dispatcher', 'views/header'], function(Marionette, K2Router, K2Controller, K2Dispatcher, HeaderView) {
	
	
	//Override the default Backbone.Sync implemetation to match Joomla! needs....
	
  // Backbone.sync
  // -------------

  // Override this function to change the manner in which Backbone persists
  // models to the server. You will be passed the type of request, and the
  // model in question. By default, makes a RESTful Ajax request
  // to the model's `url()`. Some possible customizations could be:
  //
  // * Use `setTimeout` to batch rapid-fire updates into a single request.
  // * Send up the models as XML instead of JSON.
  // * Persist models via WebSockets instead of Ajax.
  //
  // Turn on `Backbone.emulateHTTP` in order to send `PUT` and `DELETE` requests
  // as `POST`, with a `_method` parameter containing the true HTTP method,
  // as well as all requests with the body as `application/x-www-form-urlencoded`
  // instead of `application/json` with the model in a param named `model`.
  // Useful when interfacing with server-side languages like **PHP** that make
  // it difficult to read the body of `PUT` requests.
  Backbone.alla = function(method, model, options) {
    var type = methodMap[method];

    // Default options, unless specified.
    _.defaults(options || (options = {}), {
      emulateHTTP: Backbone.emulateHTTP,
      emulateJSON: Backbone.emulateJSON
    });

    // Default JSON-request options.
    var params = {type: type, dataType: 'json'};

    // Ensure that we have a URL.
    if (!options.url) {
      params.url = _.result(model, 'url') || urlError();
    }

	// Set the content type
	params.contentType = 'application/x-www-form-urlencoded';


    // Ensure that we have the appropriate request data.
    if (options.data == null && model && (method === 'create' || method === 'update' || method === 'patch')) {
      params.data = {};
      var source =  model.toJSON(options);
      _(source).each(function(value, key){
    params.data[key] = value;
});

     // Add the Joomla! session token to every request when type is not GET
     if(type !== 'GET') {
     	params.data[K2SessionToken] = 1;
     }
      
      
     
    }
    


    

     

    // For older servers, emulate HTTP by mimicking the HTTP method with `_method`
    // And an `X-HTTP-Method-Override` header.
    if (options.emulateHTTP && (type === 'PUT' || type === 'DELETE' || type === 'PATCH')) {
      params.type = 'POST';
      if (options.emulateJSON) params.data._method = type;
      var beforeSend = options.beforeSend;
      options.beforeSend = function(xhr) {
        xhr.setRequestHeader('X-HTTP-Method-Override', type);
        if (beforeSend) return beforeSend.apply(this, arguments);
      };
    }

    // Don't process data on a non-GET request.
    if (params.type !== 'GET' && !options.emulateJSON) {
      params.processData = false;
    }

    // If we're sending a `PATCH` request, and we're in an old Internet Explorer
    // that still has ActiveX enabled by default, override jQuery to use that
    // for XHR instead. Remove this line when jQuery supports `PATCH` on IE8.
    if (params.type === 'PATCH' && window.ActiveXObject &&
          !(window.external && window.external.msActiveXFilteringEnabled)) {
      params.xhr = function() {
        return new ActiveXObject("Microsoft.XMLHTTP");
      };
    }

    // Make the request, allowing the user to override any Ajax options.
    var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
    model.trigger('request', model, xhr, options);
    return xhr;
  };
	
 // Map from CRUD to HTTP for our default `Backbone.sync` implementation.
  var methodMap = {
    'create': 'POST',
    'update': 'PUT',
    'patch':  'PATCH',
    'delete': 'DELETE',
    'read':   'GET'
  };

  // Set the default implementation of `Backbone.ajax` to proxy through to `$`.
  // Override this if you'd like to use a different library.
  Backbone.ajax = function() {
    return Backbone.$.ajax.apply(Backbone.$, arguments);
  };

	
   
    

	
	

	// Initialize the application
	var K2 = new Marionette.Application();

	// Set the regions
	K2.addRegions({
		header : '#jwHeader',
		subheader : '#jwSubheader',
		sidebar : '#jwSidebar',
		content : '#jwContent',
		pagination : '#jwPagination'
	});

	// On after initialize
	K2.on('initialize:after', function() {
		Backbone.emulateHTTP = true;
		Backbone.emulateJSON = true;
		Backbone.history.start();

		// Render the header. @TODO Add intializing code for the rest regions.
		K2.header.show(new HeaderView({
			model : new Backbone.Model()
		}));
	});

	// Add initializer
	K2.addInitializer(function(options) {

		// Controller
		this.controller = new K2Controller();

		// Router
		this.router = new K2Router({
			controller : this.controller
		});

	});

	// Redirect event
	K2Dispatcher.on('app:redirect', function(url) {
		K2.router.navigate(url, {
			trigger : true
		});
	});

	// Render event
	K2Dispatcher.on('app:render', function(view) {
		K2.content.show(view);
	});

	// Return the application instance
	return K2;
});

function Status_Window ( params ) {
	var that = this;
	this.params = params;

	//objects 
	this.listings = params.listings || alert('You must attach a listings object to you status object');
	this.map = this.listings.map || alert('You need to attach a map to the listings object if you want the status object actually work');
	this.filter_position = params.fitler_position || google.maps.ControlPosition.LEFT_TOP;
	this.class = params.class || 'map_filter_area';
	this.dom_id = params.dom_id || 'map_filter_area';

	//functions representing states
	this.on_load = params.on_load || false;
	this.some_results = params.some_results || false;
	this.empty = params.empty || false;
	this.loading = params.loading || false;

	//status indicators
	this.title = false;
	this.body = false;
	this.active_title = false;
	this.active_body = false;
}

Status_Window.prototype.init = function () {
		
	if ( this.map.type == 'listings' ) {
		this.initalize_listings();
	} else if ( this.map.type == 'neighborhood' ) {
		this.initalize_neighborhood();
	} else if ( this.map.type == 'lifestyle') {
		this.initalize_lifestyle();
	} else if ( this.map.type == 'lifestyle_polygon' ) {
		this.initalize_lifestyle_polygon();
	}

}

//default initialization states
Status_Window.prototype.initalize_listings = function () {
	var that = this;
	this.on_load = this.params.on_load || function () {
		that.title = '<h4>First Load</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.some_results = this.params.some_results || function () {
		that.title = '<h4>You have Results!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.empty = this.params.empty || function () {
		that.title = '<h4>Empty!</h4>';
		that.body = 'Some text that needs to be long';
		that.update();
	}

	this.loading = this.params.loading || function () {
		that.title = '<h4>Loading</h4>';
		that.body = 'New listings are on the way!';
		that.update();
	}

	this.dragging = this.params.dragging || function () {
		that.title = '<h4>You are dragging</h4>';
		that.body = 'Let go to see new listings';
		that.update();
	}

	this.full = this.params.full || function () {
		that.body = that.active_body + ' Also, things are full here! Try zooming in.';
		that.update();
	}

	this.listeners = this.params.listeners || function () {
		jQuery('#polygon_unselect').live('click', function () {
			that.unselect_polygon();
		});
	}();

}

Status_Window.prototype.neighborhood = function () {

	Status_Window.prototype.on_load = params.on_load || function () {

	}

	Status_Window.prototype.on_load = params.some_results || function () {

	}

	Status_Window.prototype.on_load = params.empty || function () {

	}

	Status_Window.prototype.on_load = params.loading || function () {

	}	
}

Status_Window.prototype.lifestyle = function () {
	
	Status_Window.prototype.on_load = params.on_load || function () {

	}

	Status_Window.prototype.on_load = params.some_results || function () {

	}

	Status_Window.prototype.on_load = params.empty || function () {

	}

	Status_Window.prototype.on_load = params.loading || function () {

	}

}

Status_Window.prototype.lifestyle_polygon = function () {
	
	Status_Window.prototype.on_load = params.on_load || function () {

	}

	Status_Window.prototype.on_load = params.some_results || function () {

	}

	Status_Window.prototype.on_load = params.empty || function () {

	}

	Status_Window.prototype.on_load = params.loading || function () {

	}
}


Status_Window.prototype.update = function () {
	
	if ( !this.active_title || this.active_title != this.title ) {
		jQuery('#title_wrapper').html(this.title);
		this.active_title = this.title;
	}

	if ( !this.active_body || this.active_body != this.body ) {
		jQuery('#body_wrapper').html(this.body);
		this.active_body = this.body;
	}
	

	// switch ( this.map.type ) {
	// 	case 'listings':
	// 		content += '<h5>Listings Search</h5>';
	// 		content += '<p id="start_warning">Drag the map to refine your search</p>';
	// 		break;
	// 	case 'neighborhood':
	// 		content += '<h5>Neighborhood Search</h5>';
	// 		content += '<p id="start_warning">Click on a highlighted area to start searching</p>';
	// 		break;
	// }

	// var content = '<div id="polygon_display_status">';
	// if (this.map.selected_polygon) {
	// 	jQuery('#' + this.dom_id + ' #start_warning').remove();
	// 	content += '<a id="polygon_unselect">Unselect Neighborhood</a>';
	// 	content += '<div>Selected Neighborhood: ' + this.map.selected_polygon.label + '</div>';
	// 	content += '<div>Number of Listings:' + this.map.listings.ajax_response.iTotalRecords + '</div>';
	// }

	// var formatted_filters = this.get_formatted_filters();
	// if ( formatted_filters.length > 0 ) {
	// 	content += '<ul>';
	// 	for (var i = formatted_filters.length - 1; i >= 0; i--) {
	// 		content += '<li>' + formatted_filters[i].name + formatted_filters[i].value + '</li>'
	// 	};
	// 	content += '</ul>';
	// }

	// content += '</div>';
	// jQuery('#' + this.dom_id).append(content);
}

Status_Window.prototype.get_formatted_filters = function ( ) {
	var filters = this.listings.active_filters;
	var formatted_filters = [];
	for (var i = filters.length - 1; i >= 0; i--) {

		if ( ( jQuery.inArray(filters[i].name, ['metadata[beds]']) === -1 ) || filters[i].value == "")
			continue;
			
		if (this.filter_translation[filters[i].name])
			filters[i].name = this.filter_translation[filters[i].name];

		formatted_filters.push({ name: filters[i].name, value: filters[i].value })
	}
	return formatted_filters;
}

Status_Window.prototype.add_control_container = function () {
	var that = this;
	var controlDiv = document.createElement('div');
	controlDiv.id = this.dom_id;
	controlDiv.className = this.class;
	controlDiv.style.marginTop = '9px';
	controlDiv.style.marginLeft = '7px'; 
	controlDiv.style.padding = '5px';
	
	// Set CSS for the control border.
	var wrapper = document.createElement('div');
	wrapper.id = 'map_filter_area_wrapper';

	var title_wrapper = document.createElement('div');
	title_wrapper.id = 'title_wrapper';
	wrapper.appendChild(title_wrapper);

	var body_wrapper = document.createElement('div');
	body_wrapper.id = 'body_wrapper';
	wrapper.appendChild(body_wrapper);

	controlDiv.appendChild(wrapper);

	that.map.map.controls[ that.filter_position ].push(controlDiv);
}

Status_Window.prototype.unselect_polygon = function () {
	console.log(this);
	this.map.selected_polygon = false;
	this.listings.get();	
	this.map.center_on_polygons();			
	this.on_load();
}
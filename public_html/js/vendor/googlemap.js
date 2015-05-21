$(document).ready(function() {
    //var latlng = $("#gmap_latlng").val();
    var lng = $("#google-map").attr("cordX");
    var lat = $("#google-map").attr("cordY");

    var center = new google.maps.LatLng(lat, lng); // London
    //var center = new google.maps.LatLng(51.507335,-0.127683); // London

    var mapOptions = {
        zoom: 12,
        center: center,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    
    var markers = [];
    function cleanMarkers() {
        for(marker in markers) {
            markers[marker].setMap(null);
        }
    }
    
    var map = new google.maps.Map(document.getElementById("google-map"), mapOptions);
    google.maps.event.trigger(map, 'resize');
    google.maps.event.addListener(map, 'click', function(e) {
        $("#gmap_latlng").val(e.latLng.lat() + "," + e.latLng.lng());
        center = new google.maps.LatLng(e.latLng.lat(), e.latLng.lng());
        cleanMarkers();
        var marker = new google.maps.Marker({
            map: map, position: center, draggable: true
        });
        google.maps.event.addListener(marker, 'dragend', function() {
            $("#gmap_latlng").val(marker.getPosition().lat() + "," + marker.getPosition().lng());
            center = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
            map.setCenter(center);
        });
        markers.push(marker);
        map.setCenter(center);
    });
    var marker = new google.maps.Marker({
        map: map, position: center, draggable: true
    });
    google.maps.event.addListener(marker, 'dragend', function() {
        $("#gmap_latlng").val(marker.getPosition().lat() + "," + marker.getPosition().lng());
        center = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
        map.setCenter(center);
    });
    markers.push(marker);
    
    var geocoder = new google.maps.Geocoder();
    var address;
    
   /*
    if($("#gmap_latlng").val().length) {
        var latlng = $("#gmap_latlng").val();
        var coords = latlng.split(",");

        center = new google.maps.LatLng(coords[0], coords[1]);
        map.setCenter(center);

        for(marker in markers) {
            markers[marker].setMap(null);
        }

        //add marker to the map
        var marker = new google.maps.Marker({
            map: map, position: center, draggable: true
        });
        google.maps.event.addListener(marker, 'dragend', function() {
            $("#gmap_latlng").val(marker.getPosition().lat() + "," + marker.getPosition().lng());
            center = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
            map.setCenter(center);
        });
        markers.push(marker);
//               map.fitBounds(markers); 
    }
    */
   
   
    $("form #address").keypress(function(e) {
        $("#google-map").trigger("address_changed");
    });
     
    $("#google-map").bind("address_changed", function() {
        var cityOption = $("form #city_id").find("option[value="+$("form #city_id").val()+"]");
        var cityString = $(cityOption).attr("label");
        var provinceOption = $("form #province_id").find("option[value="+$("form #province_id").val()+"]");
        var provinceString = $(provinceOption).attr("label");
        var addressString = $("form #address").val();     
        var locationString = addressString + ", " + cityString + ", " + provinceString + ", Polska";

        //address = $(this).val();
        geocoder.geocode( { 'address': locationString}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                for(marker in markers) {
                    markers[marker].setMap(null);
                }
                
                center = results[0].geometry.location;
                map.setCenter(center);
                
                //add marker to the map
                var marker = new google.maps.Marker({
                    map: map, position: results[0].geometry.location, draggable: true
                });
                google.maps.event.addListener(marker, 'dragend', function() {
                    $("#gmap_latlng").val(marker.getPosition().lat() + "," + marker.getPosition().lng());
                    center = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
                    map.setCenter(center);
                });
                
                
                //var latlng = results[0].geometry.location.lat() + "," + results[0].geometry.location.lng();
                $("#cord_x").val(results[0].geometry.location.lng());
                $("#cord_y").val(results[0].geometry.location.lat());
                markers.push(marker);
//				map.addOverlay(marker);
//                markers.push(results[0].geometry.location);
//                map.fitBounds(markers); 
            }
        });
    });
    
    
});

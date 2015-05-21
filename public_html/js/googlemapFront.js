$(document).ready(function() {
    var lng = $("#google-map").attr("cordX");
    var lat = $("#google-map").attr("cordY");

    var center = new google.maps.LatLng(lat, lng); 

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
    
    var marker = new google.maps.Marker({
        map: map, position: center, draggable: true
    });
    markers.push(marker);
    
    var geocoder = new google.maps.Geocoder();
    var address;
     
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

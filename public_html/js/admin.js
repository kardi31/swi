$(document).ready(function() {
    
 /*    $("form #province_id").change(function(e) {
        var cityVal = $("form #city_id").val();
        $.ajax({
            url: "/offer/ajax/city-select-options",
            type: "get",
            data: {id: $(this).val(), format: "json" },
            dataType: "json",
            success: function(resp) {
                if(typeof(resp.options) != "undefined") {
                    $("form #city_id").html("");
                    for(key in resp.options) {
                        var option = $("<option></option>");
                        $(option).attr("value", resp.options[key]["id"]);
                        $(option).attr("label", resp.options[key]["label"]);
                        $(option).text(resp.options[key]["label"]);
                        $("form #city_id").append($(option));
                    }
                    $("form #city_id").val(cityVal);
                    $("form #city_id").change();
                }
            }
        });
    }).change(); */

    // populate form with data
    function populate(frm, data) {   
        $.each(data, function(key, value) {  
            var $ctrl = $('[name='+key+']', frm);  
            switch($ctrl.attr("type"))   {  
                case "text" :   
                case "hidden":  
                case "textarea":  
                $ctrl.val(value);   
                break;   
                case "radio" : case "checkbox":   
                $ctrl.attr("checked",value);  
                break;  
            }  
        });  
    }

        
    $("table").delegate("a.remove", "click", function(e) {
        if(!confirm("Czy na pewno chcesz usunąć?")) {
            e.preventDefault();
        }
//        e.preventDefault();
//        if(confirm("Czy na pewno chcesz usunąć?")) {
//            window.location.href = $(this).attr("href");
//        }
    });
    
    
    
    $("#category_tree").dynatree({
        onClick: function(node, event) {
            if(node.getEventTargetType(event) == "null"){
                $("#category_tree").dynatree("getActiveNode").deactivate();
                return false;
//                var id = node.data.key.replace("node", "");
//                node.activate();
//                return false;// Prevent default processing
            }
        },
        onActivate: function(node) {
            if( node.data.href ){
                window.open(node.data.href, '_self');
            }
        },
        minExpandLevel: 2,
        autoExpandMS: 1000,
        expand: false,
        preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
        dnd: {
            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
            onDragStart: function(node) {
                /** This function MUST be defined to enable dragging for the tree.
                *  Return false to cancel dragging of node.
                */
                return true;
            },
            onDragEnter: function(node, sourceNode) {
                /** sourceNode may be null for non-dynatree droppables.
                *  Return false to disallow dropping on node. In this case
                *  onDragOver and onDragLeave are not called.
                *  Return 'over', 'before, or 'after' to force a hitMode.
                *  Return ['before', 'after'] to restrict available hitModes.
                *  Any other return value will calc the hitMode from the cursor position.
                */
                // Prevent dropping a parent below another parent (only sort
                // nodes under the same parent)
                if(node.parent !== sourceNode.parent){
//                return false;
                }
                // Don't allow dropping *over* a node (would create a child)
                return ["before", "after", "over"];
            },
            onDragOver: function(node, sourceNode, hitMode) {
                /** Return false to disallow dropping this node.
                *
                */
                logMsg("tree.onDragOver(%o, %o, %o)", node, sourceNode, hitMode);
                // Prevent dropping a parent below it's own child
                if(node.isDescendantOf(sourceNode)){
                    return false;
                }
                // Prohibit creating childs in non-folders (only sorting allowed)
//                if( !node.data.isFolder && hitMode === "over" ){
//                    return "after";
//                }
            },
            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
                /** This function MUST be defined to enable dropping of items on
                *  the tree.
                */
               if(node.data.key == "trash") {
                   if(!confirm('Czy na pewno chcesz usunąć?')) {
                       return false;
                   }
                   $.ajax({
                       url: "/admin/product/remove-category",
                       type: "post",
                       dataType: "json",
                       data: { id: sourceNode.data.key.replace("node", ""), format: "json" },
                       success: function(resp) {
                           if(resp.status == "success") {
                               sourceNode.remove();
                               
                               $("#category_table").trigger("update");
                           }
                       }
                   });
               } else {
                   $.ajax({
                       url: "/admin/product/move-category",
                       type: "post",
                       dataType: "json",
                       data: { id: sourceNode.data.key.replace("node", ""), dest_id: node.data.key.replace("node", ""), mode: hitMode, format: "json" },
                       success: function(resp) {
                           if(resp.status == "success") {
                               sourceNode.move(node, hitMode);
                               node.expand();
                               
                               $("#category_table").trigger("update");
                           }
                       }
                   });
               }
            },
            onDragLeave: function(node, sourceNode) {
                /** Always called if onDragEnter was called.
                */
                logMsg("tree.onDragLeave(%o, %o)", node, sourceNode);
            }
        }
//        dnd: {
//            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
//            onDragStart: function(node) {
//            return true;
//        },
//            onDragEnter: function(node, sourceNode) {
//            if(node.parent !== sourceNode.parent)
//                return false;
//            return ["before", "after"];
//        },
//            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
//            sourceNode.move(node, hitMode);
//        }
//        dnd: {
//            onDragStart: function(node) {
//                /** This function MUST be defined to enable dragging for the tree.
//                 *  Return false to cancel dragging of node.
//                 */
//                logMsg("tree.onDragStart(%o)", node);
//                return true;
//            },
//            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
//                /** This function MUST be defined to enable dropping of items on
//                 * the tree.
//                 */
//                logMsg("tree.onDrop(%o, %o, %s)", node, sourceNode, hitMode);
//                sourceNode.move(node, hitMode);
//            }
//        }
    });

    $("#menu_tree").dynatree({
        onClick: function(node, event) {
            if(node.getEventTargetType(event) == "null"){
                $("#category_tree").dynatree("getActiveNode").deactivate();
                return false;
//                var id = node.data.key.replace("node", "");
//                node.activate();
//                return false;// Prevent default processing
            }
        },
        onActivate: function(node) {
            if( node.data.href ){
                window.open(node.data.href, '_self');
            }
        },
        minExpandLevel: 10,
        autoExpandMS: 1000,
        preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
        dnd: {
            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
            onDragStart: function(node) {
                /** This function MUST be defined to enable dragging for the tree.
                *  Return false to cancel dragging of node.
                */
                return true;
            },
            onDragEnter: function(node, sourceNode) {
                /** sourceNode may be null for non-dynatree droppables.
                *  Return false to disallow dropping on node. In this case
                *  onDragOver and onDragLeave are not called.
                *  Return 'over', 'before, or 'after' to force a hitMode.
                *  Return ['before', 'after'] to restrict available hitModes.
                *  Any other return value will calc the hitMode from the cursor position.
                */
                // Prevent dropping a parent below another parent (only sort
                // nodes under the same parent)
                if(node.parent !== sourceNode.parent){
//                return false;
                }
                // Don't allow dropping *over* a node (would create a child)
                return ["before", "after", "over"];
            },
            onDragOver: function(node, sourceNode, hitMode) {
                /** Return false to disallow dropping this node.
                *
                */
                logMsg("tree.onDragOver(%o, %o, %o)", node, sourceNode, hitMode);
                // Prevent dropping a parent below it's own child
                if(node.isDescendantOf(sourceNode)){
                    return false;
                }
                // Prohibit creating childs in non-folders (only sorting allowed)
//                if( !node.data.isFolder && hitMode === "over" ){
//                    return "after";
//                }
            },
            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
                /** This function MUST be defined to enable dropping of items on
                *  the tree.
                */
               if(node.data.key == "trash") {
                   if(!confirm('Czy na pewno chcesz usunąć?')) {
                       return false;
                   }
                   $.ajax({
                       url: "/admin/product/remove-category",
                       type: "post",
                       dataType: "json",
                       data: { id: sourceNode.data.key.replace("node", ""), format: "json" },
                       success: function(resp) {
                           if(resp.status == "success") {
                               sourceNode.remove();
                               
                               $("#category_table").trigger("update");
                           }
                       }
                   });
               } else {
                   $.ajax({
                       url: "/admin/menu/move-menu-item",
                       type: "post",
                       dataType: "json",
                       data: { id: sourceNode.data.key.replace("node", ""), dest_id: node.data.key.replace("node", ""), mode: hitMode, format: "json" },
                       success: function(resp) {
                           if(resp.status == "success") {
                               sourceNode.move(node, hitMode);
                               node.expand();
                               
                               $("#menu_tree").trigger("update");
                           }
                       }
                   });
               }
            },
            onDragLeave: function(node, sourceNode) {
                /** Always called if onDragEnter was called.
                */
                logMsg("tree.onDragLeave(%o, %o)", node, sourceNode);
            }
        }
//        dnd: {
//            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
//            onDragStart: function(node) {
//            return true;
//        },
//            onDragEnter: function(node, sourceNode) {
//            if(node.parent !== sourceNode.parent)
//                return false;
//            return ["before", "after"];
//        },
//            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
//            sourceNode.move(node, hitMode);
//        }
//        dnd: {
//            onDragStart: function(node) {
//                /** This function MUST be defined to enable dragging for the tree.
//                 *  Return false to cancel dragging of node.
//                 */
//                logMsg("tree.onDragStart(%o)", node);
//                return true;
//            },
//            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
//                /** This function MUST be defined to enable dropping of items on
//                 * the tree.
//                 */
//                logMsg("tree.onDrop(%o, %o, %s)", node, sourceNode, hitMode);
//                sourceNode.move(node, hitMode);
//            }
//        }
    });
    
    //google maps producer
    if (typeof(google) != "undefined"){
        var lng = $("#cord_x").val();
        var lat = $("#cord_y").val();

        var mapCenter = new google.maps.LatLng(lat, lng); // London
    //    var mapCenter = new google.maps.LatLng(51.507335,-0.127683); // London

        var mapOptions = {
            zoom: 12,
            mapCenter: mapCenter,
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
        map.setCenter(mapCenter);

        google.maps.event.addListener(map, 'click', function(e) {

            mapCenter = new google.maps.LatLng(e.latLng.lat(), e.latLng.lng());
            $("#cord_x").val(e.latLng.lng());
            $("#cord_y").val(e.latLng.lat());
            cleanMarkers();
            var marker = new google.maps.Marker({
                map: map, position: mapCenter, draggable: true
            });
            google.maps.event.addListener(marker, 'dragend', function() {
                $("#cord_x").val(marker.getPosition().lng());
                $("#cord_y").val(marker.getPosition().lat());
                mapCenter = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
                map.setCenter(mapCenter);
            });
            markers.push(marker);
            map.setCenter(mapCenter);
        });
        var marker = new google.maps.Marker({
            map: map, position: mapCenter, draggable: true
        });
        google.maps.event.addListener(marker, 'dragend', function() {
            $("#cord_x").val(marker.getPosition().lng());
            $("#cord_y").val(marker.getPosition().lat());
            mapCenter = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
            map.setCenter(mapCenter);
        });
        markers.push(marker);

        var geocoder = new google.maps.Geocoder();
        var address;


        $("form #address").keypress(function(e) {
            $("#google-map").trigger("address_changed");
        });

        $("#google-map").bind("address_changed", function() {
            // var cityOption = $("form #city_id").find("option[value="+$("form #city_id").val()+"]");
            // var cityString = $(cityOption).attr("label");
            var cityString = $("form #city").val();
            //var provinceOption = $("form #province_id").find("option[value="+$("form #province_id").val()+"]");
            //var provinceString = $(provinceOption).attr("label");
            var provinceString = $("form #province").val();
            var addressString = $("form #address").val();     
            var locationString = addressString + ", " + cityString + ", " + provinceString + ", Polska";
            //address = $(this).val();
            geocoder.geocode( { 'address': locationString}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    for(marker in markers) {
                        markers[marker].setMap(null);
                    }

                    mapCenter = results[0].geometry.location;
                    map.setCenter(mapCenter);

                    //add marker to the map
                    var marker = new google.maps.Marker({
                        map: map, position: results[0].geometry.location, draggable: true
                    });
                    google.maps.event.addListener(marker, 'dragend', function() {
                        $("#cord_x").val(marker.getPosition().lng());
                        $("#cord_y").val(marker.getPosition().lat());
                        mapCenter = new google.maps.LatLng(marker.getPosition().lat(), marker.getPosition().lng());
                        map.setCenter(mapCenter);
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
    }
    
});
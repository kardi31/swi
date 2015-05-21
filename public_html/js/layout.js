var kalwarysjkiCfg = {
    bannerWidth:936,
    bannerInterval:5000,
    paginationInterval:5000,
    bannerSpd:500,
    manThumbSpd:500,
    guestSpd:500,
    productPicW:660,
    productSpd:300,
    tabW:980,
    tabSpd:500,
    pageW:980,
    pageSpd:500,
    scrlTopSpd:500
}

var windowWidth = $(window).width();

var categoryDropdown = function(){
    var catDropdown = $(".categoryDropdown");
    if(catDropdown.length >0){
        var catDropdownList = $(".categoryDropdown ul");
        $(".categoryDropdown .toggle").on('click',function(){
            $(this).toggleClass("collapsed");
            catDropdownList.toggleClass("collapsed");
        })
    }
}
var headerBanner = function(cfg){
    var bannerObj = $("#banner");
    if(bannerObj.length > 0){
        var slidesCollection = $("#banner .frame .viewport .inner .slide");
        var locked = false;
        var dotContainer = $("#banner .overlay .bannerDots");
        var firstDot=" curr";
        slidesCollection.each(function(index){
            //position slides
            if(index==0){
                $(this).css({"position":"absolute","left":0});
            }
            else{
                $(this).css({"position":"absolute","left":cfg.bannerWidth});
            }
            //create navigation dots
            dotContainer.append('<div class="dot'+firstDot+'"></div>');
            firstDot="";
        });
        
        var dotsCollection = $("#banner .overlay .bannerDots .dot");
        //bind events
        dotsCollection.each(function(index){
            $(this).on("click",function(){
                if(!locked && !$(this).hasClass("curr")){
                    clearTimeout(bannerTimer);
                    animateBanner(index);
                }
            })
        })
        
        var currSlide = 0;
        var lastSlide = slidesCollection.length;
        var bannerTimer;
        
        //define animation
        var animateBanner = function(next){
            locked = true;
            clearDots();
            dotsCollection.eq(next).addClass("curr");
            slidesCollection.eq(next).addClass("toFront");
            //prepare next
            slidesCollection.eq(next).css("left",cfg.bannerWidth);
            //move current
            slidesCollection.eq(currSlide).animate({"left":-cfg.bannerWidth},cfg.bannerSpd,function(){
                slidesCollection.eq(currSlide).removeClass("toFront");
                locked=false;
                currSlide = next;
            })
            //move next
            slidesCollection.eq(next).animate({"left":0},cfg.bannerSpd);
            //schedule animation
            changeSlide(next+1)
        }
        
        var clearDots = function(){
            dotsCollection.each(function(index){
                $(this).removeClass('curr');
            })
        }
        //schedule animation
        var bannerTimer;
        var changeSlide = function(next){
            if(next==lastSlide){
                next=0;
            }
            bannerTimer = setTimeout(function(){
                animateBanner(next);
            },cfg.bannerInterval)
        }
        changeSlide(1);
       
        $(window).on("blur",function(){
            clearTimeout(bannerTimer);
        })
        $(window).on("focus",function(){
           clearTimeout(bannerTimer);
           changeSlide(currSlide+1);
        })
       
    }
}
var applyTransitions = function(targ,cfg){
    //targ = collection of jequery thumbContainer objects
    if(Modernizr.csstransitions){
        targ.each(function(){
            if($(this).find("img").length > 1){
                $(this).addClass("transition");
            }
        });
    }
    else{
        targ.each(function(){
            if($(this).find("img").length > 1){
                $(this).on("mouseover",function(){
                    $(this).stop();
                    $(this).animate({"margin-left":"-100%"},cfg.manThumbSpd);
                });
                $(this).on("mouseleave",function(){
                    $(this).stop();
                    $(this).animate({"margin-left":"0%"},cfg.manThumbSpd);
                });
            }
        });
    }
}

var findThumbnails = function(where){
    return where.find(".thumbContainer");
}

var applyTransitionsProducers = function(targ,cfg){
    //targ = collection of jequery thumbContainer objects

    $(targ).each(function() {
                $(this).on("mouseenter",function(){ 
                    locked=true;
                    $(".productsContainer.home").css("overflow", 'visible');
                    $(targ).each(function(i, elem) {
                        $(elem).css("z-index", "100");
                    });
                    $(this).css("z-index", "200");
                  
                    $(this).stop();
                    $(this).animate({width:"150%", height:"150%", "margin-left":"-25%", "margin-top":"-17%"},cfg.manThumbSpd);
                });
                $(this).on("mouseleave",function(){
                    locked=false;
                    $(".productsContainer.home").css("overflow", 'visible');
                    $(targ).each(function(i, elem) {
                        $(elem).css("z-index", "100");
                    });
                    $(this).stop();
                    $(this).animate({width:"100%", height:"100%", "margin-left":"0", "margin-top":"0"},cfg.manThumbSpd);
                });
    });


}

var findThumbnailsProducers = function(where){
    return where.find(".thumbContainerProducers");
}

var guestbook = function(cfg){
    if($("#guestBook")){ 
        var guestControls = $(".guestNav li");
        var entriesNo = $(".guestEntries article").length-1;
        var currEntry=0;
        var guestList = $(".guestEntries");
        var guestForm = $("#guestBook form");
        
        var moveEntry = function(dir){
            var margin = dir*306;
            guestList.animate({"margin-left":"+=margin"},cfg.guestSpd);
        }
        var toggleForm = function(){
            guestForm.toggleClass("hidden");
            $(".guestNav").toggleClass("hidden");
            guestList.toggleClass("hidden");
        }
        guestControls.eq(0).find("a").on("click",function(event){
            event.preventDefault();
            
            if(currEntry!=0){
                if (windowWidth > 979){
                    guestList.animate({"margin-left":"+=306"},cfg.guestSpd);        
                }
                if (windowWidth > 767 && windowWidth <= 979){
                    guestList.animate({"margin-left":"+=206"},cfg.guestSpd);   
                }                
                currEntry--;
            }
        })
        guestControls.eq(1).find("a").on("click",function(event){
            event.preventDefault();
            toggleForm();
        })
        guestControls.eq(2).find("a").on("click",function(event){
            event.preventDefault();
            if(currEntry<entriesNo){
                                if (windowWidth > 979){
                    guestList.animate({"margin-left":"-=306"},cfg.guestSpd);        
                }
                if (windowWidth > 767 && windowWidth <= 979){
                    guestList.animate({"margin-left":"-=206"},cfg.guestSpd);   
                }   
                currEntry++;
            }
        })
        $("#guestBook .cancel").on("click",function(event){
            event.preventDefault();
            toggleForm();
        })
    }
}

//var leSort = function(targ,type,dir){
var leSort = function(targ,caller){
    var indicators = $('.sortBar .sort');
    var type = caller.data("sort");
    var dir = caller.hasClass("asc");
    var shorTable=[];
//    var sortable = $(".man article");
    var sortable = $(targ);
    
    sortable.each(function(index){
        $(this).attr("data-order",index);
        shorTable.push({"product":$(this).find($(".productName")).html(),"man":$(this).find($(".manName")).html(),"index":index,"jqObj":$(this)});
    })
    function sortProd(a,b){
        return a.product.localeCompare(b.product);
        //return a.product.toLowerCase() > b.product.toLowerCase() ? 1 : -1;  
    }; 
    function sortProdDesc(a,b){
        return - a.product.localeCompare(b.product);
        //return a.product.toLowerCase() < b.product.toLowerCase() ? 1 : -1;  
    }; 
    function sortMan(a,b){
        return a.man.localeCompare(b.man);
       // return a.man.toLowerCase() > b.man.toLowerCase() ? 1 : -1;  
    }; 
    function sortManDesc(a,b){  
        return - a.man.localeCompare(b.man);
        //return a.man.toLowerCase() < b.man.toLowerCase() ? 1 : -1;  
    }; 
    indicators.removeClass('curr');
    if(type == "man"){
        if(dir) {
            shorTable.sort(sortMan);
            indicators.eq(2).addClass('curr');
        } else {
            shorTable.sort(sortManDesc);
            indicators.eq(3).addClass('curr');
        }
//        dir?shorTable.sort(sortMan):shorTable.sort(sortManDesc);
//        dir?indicators.eq(2).addClass('curr'):indicators.eq(3).addClass('curr');
    }
    else{
        if(dir) {
            shorTable.sort(sortProd);
            indicators.eq(0).addClass('curr');
        } else {
            shorTable.sort(sortProdDesc);
            indicators.eq(1).addClass('curr');
        }
//        dir?shorTable.sort(sortProd):shorTable.sort(sortProdDesc);
//        dir?indicators.eq(0).addClass('curr'):indicators.eq(1).addClass('curr');
    }
//    shorTable.sort(sortAlpha)

    for(var i=0;i<shorTable.length;i++){
        shorTable[i].jqObj.appendTo($("#productsContainer .page"));
    }
    if(caller.hasClass("asc")) {
        caller.removeClass("asc");
        caller.addClass("desc");
    } else {
        caller.removeClass("desc");
        caller.addClass("asc");
    }
//    caller.toggleClass("asc")
    console.log(dir)
}

var getCategories = function(){
    $(".middle .categories").load("index.html .middle.main article");
    //console.log($.load)
//    $.ajax({
//        url: "index.html",
//        dataType:"html",
//        context: $(".middle .categories")
//        }).done(function(data) {
//        //$(this).addClass("done");
//        var results = $.parseHTML(data);
//        
//       // console.log(results.find(".man article"))
//    });
    
}

var productGallery = function(cfg){
    var thumbs = $(".tab.details .thumbs img");
    if(thumbs.length>0){
        var medPics = $(".tab.details .viewport img");
        var locked = false;
        medPics.each(function(index){
            if(index==0){
                $(this).addClass("current");
            }
            else{
                $(this).css("left",cfg.productPicW)
            }
        })
        thumbs.each(function(index){
            $(this).on("click",function(){
                if(!medPics.eq(index).hasClass("current")&&!locked){
                    locked=true;
                    $(".tab.details .viewport .current").animate({"left":-cfg.productPicW},cfg.productSpd,function(){
                        $(this).removeClass("current").css("left",cfg.productPicW);
                        locked=false;
                    })
                    medPics.eq(index).animate({"left":0},cfg.productSpd,function(){
                        $(this).addClass("current");
                    })
                }
            })
        })
    }
    
}

var tabNavigation = function(cfg){
    var tabNav = $(".manNav li");
    if(tabNav.length>0){
        var tabs = $(".tabContainer .tab");
        var locked = false;
        tabNav.each(function(index){
            $(this).on("click",function(){
                if(!$(this).hasClass("current")&&!locked){
                    $(".manNav li.current").removeClass("current");
                    locked=true;
                    $(this).addClass("current");
                    $(".tabContainer .tab.current").animate({"left":-cfg.tabW},cfg.tabSpd,function(){
                        $(this).removeClass("current").css("left",cfg.tabW);
                        locked=false;
                    })
                    tabs.eq(index).animate({"left":0},cfg.tabSpd,function(){
                        $(this).addClass("current");
                    })
                }
            })
//            
            //disable anchors
            $(this).find("a").on("click",function(event){
                event.preventDefault();
            })
        })
    }
    
}

var moveToContact = function(){
    $("#contactTrigger").click(function() {
    var tabs = $(".tabContainer .tab");
    var locked = false;
        $(".manNav ul li").removeClass("current");
        $(tabs).removeClass("current");
        $(".manNav ul li").eq(3).addClass("current");
         $(tabs).removeAttr("style");
        $(tabs).eq(3).addClass("current");
    });
}

var homePagination = function(cfg){
    var homeNav = $(".productsContainer.home .paginationDots .dot");
    if(homeNav.length>0){
        for(var i=0;i<homeNav.length-1;i++){
            $(".productsContainer.home .viewport").append('<div class="page"></div>');
        }
        var pages = $(".productsContainer.home .page");
        pages.each(function(index){
            if(index!=0){
                $(this).css("left",cfg.pageW);
            }
        })
        $(".productsContainer.home .inner").css("width",1000*homeNav.length)
        var locked = false;
        homeNav.each(function(index){
            //todo: clean this up...
            $(this).on("click",function(event){
                event.preventDefault();
                if(!$(this).hasClass("current")&&!locked){
                    if(pages.eq(index).find("article").length>0){
                        locked=true;
                        $(".paginationDots .dot.current").removeClass("current");
                        $(".productsContainer.home").css("overflow", 'hidden');
                        $(this).addClass("current");
                        $(".productsContainer.home .page.current").animate({"left":-cfg.pageW},cfg.pageSpd,function(){
                            $(this).removeClass("current").css("left",cfg.tabW);
                            $(this).hide();
                            locked=false;
                        })
                        pages.eq(index).show();
                        pages.eq(index).animate({"left":0},cfg.pageSpd,function(){
                            $(this).addClass("current");
                            $(".productsContainer.home").css("overflow", 'visible');
                        });
                    }
                    else{
                        locked=true;
                        that=$(this);
                        pages.eq(index).load($(this).attr("href"), function(){
                            $(".paginationDots .dot.current").removeClass("current");
                            locked=true;
                            $(".productsContainer.home").css("overflow", 'hidden');
                            that.addClass("current");
                            $(".productsContainer.home .page.current").animate({"left":-cfg.pageW},cfg.pageSpd,function(){
                                $(this).removeClass("current").css("left",cfg.tabW);
                                $(this).hide();
                                locked=false;
                            })
                            pages.eq(index).show();
                            pages.eq(index).animate({"left":0},cfg.pageSpd,function(){
                                $(this).addClass("current");
                                $(".productsContainer.home").css("overflow", 'visible');
                            })
                            applyTransitions(findThumbnails($("#productsContainer")),kalwarysjkiCfg);
                            if( navigator.appName != "Microsoft Internet Explorer" && windowWidth > 979 ) {
                                applyTransitionsProducers(findThumbnailsProducers($("#productsContainer")),kalwarysjkiCfg);
                            }                    
                        });
                        
                        
                    }
                }
            });
        });
    }
}
var photoLayer = function(smallPics){
    var photoLayer = $(".photoLayer");
    //todo check not only photolayer but also triggering elements - thumbs and med-sized imgs
    if(photoLayer.length>0){
        var imgData = [];
//        $(".tab.details .viewport a").each(function(){
        smallPics.each(function(){
            imgData.push({"url":$(this).attr("href")})
        })
//        $(".tab.about .thumbs a").each(function(){
//            imgData.push({"url":$(this).attr("href")})
//        })
//        
//        $(".tab.details .viewport a").on("click",function(event){
        smallPics.on("click",function(event){
            event.preventDefault();
            if(imgData[$(this).index()].img){
                photoLayer.empty().append(imgData[$(this).index()].img).removeClass("hidden");
            }
            else{
                var imgObj = imgData[$(this).index()].img;
                var that = $(this);
                imgObj = new Image();
                imgObj.onload = function(){
                    imgData[that.index()].img = this;
                    photoLayer.empty().append(imgData[that.index()].img).removeClass("hidden");
                }
                imgObj.src = imgData[$(this).index()].url;
            }
            
        })
        photoLayer.on("click",function(){
            $(this).addClass("hidden");
        })
    }
    
}

var autoMain = function(cfg){
    var mainTimer;
    var pageDots=$('.paginationDots .dot');
    var autoMainLocked = false;
    $(".productsContainer.home .page").each(function(i, elem) {
        if(i > 0) {
            $(elem).hide();
        }
    });
    if(pageDots.length>0){
        $('.productsContainer.home').on('mouseenter',function(){
            autoMainLocked = true;
        })
        $('.productsContainer.home').on('mouseleave',function(){
            autoMainLocked = false;
        })
        var animatePage = function(){
            if(!autoMainLocked){
                pageDots.each(function(index){
                    if($(this).hasClass('current')){
                        if(index === pageDots.length-1){ 
                            pageDots.eq(0).trigger('click');
                        }
                        else{
                            pageDots.eq(index+1).trigger('click')
                        }
                    }

                })
            }
            changePage();
        }

        var changePage = function(){
            mainTimer = setTimeout(function(){
                animatePage();
            },cfg.paginationInterval)
        }

        changePage();

        $(window).on("blur",function(){
            clearTimeout(mainTimer);
        })
        $(window).on("focus",function(){
            clearTimeout(mainTimer);
            changePage();
        })
    }

}

moveToContact();
categoryDropdown();
headerBanner(kalwarysjkiCfg);
applyTransitions(findThumbnails($("#productsContainer")),kalwarysjkiCfg);
if( navigator.appName != "Microsoft Internet Explorer" && windowWidth > 979) {
  applyTransitionsProducers(findThumbnailsProducers($("#productsContainer")),kalwarysjkiCfg);
}
guestbook(kalwarysjkiCfg);
productGallery(kalwarysjkiCfg);
tabNavigation(kalwarysjkiCfg);
homePagination(kalwarysjkiCfg);
autoMain(kalwarysjkiCfg);
//leSort(".man article")
$(".sort").on("click",function(event){
    event.preventDefault();
})
$(".sortBy").on("click",function(event){
    event.preventDefault();
//    leSort(".man article",$(this).data("sort"),$(this).hasClass("asc"));
    leSort("#productsContainer article",$(this));
});

//photoLayer($(".tab.details .viewport a"));
//photoLayer($(".tab.about .thumbs a"));
photoLayer($(".qrGps a"));
photoLayer($(".manufacturerData .manHeader .qr a"));

$(".showTop").on('click',function(){
//    scrollTo(0,0);
    $('html, body').animate({'scrollTop':0},kalwarysjkiCfg.scrlTopSpd);
})

	

//test ajaxa ponizej:
//$("#test1").on("click",function(){
//getCategories()
//})

//test sortowania ponizej:
//$(".title").on("click",function(){leSort();});


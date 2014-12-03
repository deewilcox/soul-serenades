/* Image click fix */
(function($) {
    "use strict";
    $(".tm-extra-product-options .use_images_containter .tmcp-field-wrap label").on("click", function() {        
        return false;
    });
    $(".tm-extra-product-options label img").on("click", function() {
        var label=$(this).closest("label");
        
        var box=$("#" + label.attr("for"));
        var _check=false;
        if ($(box).is(":checked")){
            _check=true;                                
        }
        if (!_check){
            $(box).attr("checked","checked").prop("checked",true);
        }else{
            $(box).removeAttr("checked").prop("checked",false);
        }
        $(box).trigger('change').trigger('tmredirect');
    });

})(jQuery);

// http://paulirish.com/2011/requestanimationframe-for-smart-animating/
// http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating
// requestAnimationFrame polyfill by Erik MΓ¶ller. fixes from Paul Irish and Tino Zijdel
// MIT license
(function() {
    "use strict";

    var lastTime = 0;
    var vendors = ['ms', 'moz', 'webkit', 'o'];
    for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
        window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
        window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] || window[vendors[x] + 'CancelRequestAnimationFrame'];
    }

    if (!window.requestAnimationFrame)
        window.requestAnimationFrame = function(callback, element) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max(0, 16 - (currTime - lastTime));
            var id = window.setTimeout(function() {
                callback(currTime + timeToCall);
            },
            timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        };
    if (!window.cancelAnimationFrame)
        window.cancelAnimationFrame = function(id) {
            clearTimeout(id);
        };
}());

/**
* jquery.resizestop (and resizestart)
* by: Fatih Kadir Akın
*
* License is CC0, published to the public domain.
*/
(function(a){var b=Array.prototype.slice;a.extend(a.event.special,{resizestop:{add:function(d){var c=d.handler;a(this).resize(function(f){clearTimeout(c._timer);f.type="resizestop";var g=a.proxy(c,this,f);c._timer=setTimeout(g,d.data||200)})}},resizestart:{add:function(d){var c=d.handler;a(this).on("resize",function(f){clearTimeout(c._timer);if(!c._started){f.type="resizestart";c.apply(this,arguments);c._started=true}c._timer=setTimeout(a.proxy(function(){c._started=false},this),d.data||300)})}}});a.extend(a.fn,{resizestop:function(){a(this).on.apply(this,["resizestop"].concat(b.call(arguments)))},resizestart:function(){a(this).on.apply(this,["resizestart"].concat(b.call(arguments)))}})})(jQuery);

(function($) {
    "use strict";

    if (!$.is_on_screen) {
        $.fn.is_on_screen = function(){
            var win = $(window);
            var u = $.tm_getPageScroll();
            var viewport = {
                top : u[1],
                left : u[0]
            };
            viewport.right = viewport.left + win.width();
            viewport.bottom = viewport.top + win.height();
         
            var bounds = this.offset();
            bounds.right = bounds.left + this.outerWidth();
            bounds.bottom = bounds.top + this.outerHeight();
         
            return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
        };
    }

    if (!$.tm_tooltip) {
        $.tm_tooltip = function(jobj) {
            if (typeof jobj === 'undefined') {
                jobj = $( '.tm-tooltip' );
            }
            var targets = jobj,
                target  = false,
                tooltip = false,
                title   = false;
            if (!targets.length>0){
                return;
            }
            targets.each(function(i,el){
                var current_element = $(el);
                var is_swatch = current_element.attr( 'data-tm-tooltip-swatch' );
                if (is_swatch){
                    var label=current_element.closest('.tmcp-field-wrap').find('.checkbox_image_label');
                    var tip=$(label).html();
                    current_element.data('tm-tip-html',tip);
                    $(label).hide();
                }

            });
            targets.on( 'mouseenter tmshowtooltip', function(){
                
                target  = $( this );
                if (target.data('is_moving')){
                    return;
                }
                var tip     = target.attr( 'title' );
                var is_swatch = target.attr( 'data-tm-tooltip-swatch' );
                tooltip = $( '<div id="tm-tooltip" class="tm-tip"></div>' );
                
                if( !((tip && tip != '') || is_swatch )){
                    return false;
                }
                
                if (is_swatch){
                    tip=target.data('tm-tip-html');
                }
                if (typeof jobj === 'undefined'){
                    target.removeAttr( 'title' );
                }
                tooltip.css( 'opacity', 0 )
                       .html( tip )
                       .appendTo( 'body' );
         
                var init_tooltip = function(nofx){
                    if (nofx==1){
                        if (is_swatch){
                            tip=target.data('tm-tip-html');
                        }else{
                            tip = target.attr( 'title' );
                        }
                        tooltip.html(tip);    
                    }
                    
                    if( $( window ).width() < tooltip.outerWidth() * 1.5 ){
                        tooltip.css( 'max-width', $( window ).width() / 2 );
                    }else{
                        tooltip.css( 'max-width', 340 );
                    }
                    var u = $.tm_getPageScroll();
                    var pos_left = target.offset().left + ( target.outerWidth() / 2 ) - ( tooltip.outerWidth() / 2 ),
                        pos_top  = target.offset().top - tooltip.outerHeight() - 20;
                    //tooltip.html(target.offset().top-u[1]);
                    var pos_from_top=target.offset().top-u[1]-tooltip.outerHeight();
                    
                    if( pos_left < 0 ){
                        pos_left = target.offset().left + target.outerWidth() / 2 - 20;
                        tooltip.addClass( 'left' );
                    }else{
                        tooltip.removeClass( 'left' );
                    }
                    if( pos_left + tooltip.outerWidth() > $( window ).width() ){
                        pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
                        tooltip.addClass( 'right' );
                    }else{
                        tooltip.removeClass( 'right' );
                    }
                    if( pos_top < 0 || pos_from_top < 0){
                        pos_top  = target.offset().top + target.outerHeight();
                        tooltip.addClass( 'top' );
                    }else{
                        tooltip.removeClass( 'top' );
                    }
                    var speed=50;
                    if (nofx){
                        tooltip.css( { left: pos_left, top: (pos_top+10) } ); 
                        target.data('is_moving',false);                       
                    }else{
                        tooltip.css( { left: pos_left, top: pos_top } )
                           .animate( { top: '+=10', opacity: 1 }, speed );
                    }
                };
         
                init_tooltip();
                $( window ).resize( init_tooltip );
                target.data('is_moving',false);
                var remove_tooltip = function(){
                    if (target.data('is_moving')){
                        return;
                    }
                    tooltip.animate( { top: '-=10', opacity: 0 }, 50, function(){
                        $( this ).remove();
                    });
         
                    target.attr( 'title', tip );
                };

                target.on( 'tmmovetooltip', function(){target.data('is_moving',true);init_tooltip(1);} );
                target.on( 'mouseleave tmhidetooltip', remove_tooltip );
                tooltip.on( 'click', remove_tooltip );
            });
            return targets;
        }
    }

    $.fn.aserializeArray = function() {
        var rselectTextarea = /^(?:select|textarea)/i,
            rinput = /^(?:color|date|datetime|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i;
        if (!this.get(0).elements) {
            $(this).wrap('<form></form>');
            var varretval = this.parent().map(function() {
                return this.elements ? $.makeArray(this.elements) : this;
            }).filter(function() {
                return this.name && !this.disabled && (this.checked || rselectTextarea.test(this.nodeName) || rinput.test(this.type));
            }).map(function(i, elem) {
                var val = $(this).val();
                return val == null ? null : $.isArray(val) ? $.map(val, function(val, i) {
                    return {
                        name: elem.name,
                        value: val
                    };
                }) : {
                    name: elem.name,
                    value: val
                };
            }).get();
            $(this).unwrap();
            return varretval;
        } else {
            return this.map(function() {
                return this.elements ? $.makeArray(this.elements) : this;
            }).filter(function() {
                return this.name && !this.disabled && (this.checked || rselectTextarea.test(this.nodeName) || rinput.test(this.type));
            }).map(function(i, elem) {
                var val = $(this).val();
                return val == null ? null : $.isArray(val) ? $.map(val, function(val, i) {
                    return {
                        name: elem.name,
                        value: val
                    };
                }) : {
                    name: elem.name,
                    value: val
                };
            }).get();
        }
    }
    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    }
    

    if (!$().on) {
        $.fn.on = function(types, selector, data, fn) {
            return this.delegate(selector, types, data, fn);
        }
    }

    /* https://github.com/kvz/phpjs/blob/master/functions/array/array_values.js */
    if (!$.tm_array_values) {
        $.tm_array_values = function(input) {
            var tmp_arr = [], key = '';
            for (key in input) {
                tmp_arr[tmp_arr.length] = input[key];
            }
            return tmp_arr;
        }
    }

    /* https://github.com/kvz/phpjs/blob/master/functions/misc/uniqid.js */
    if (!$.tm_uniqid) {
        $.tm_uniqid = function(prefix, more_entropy) {
            if (typeof prefix === 'undefined') {
                prefix = '';
            }
            var retId;
            var formatSeed = function (seed, reqWidth) {
                seed = parseInt(seed, 10)
                  .toString(16); // to hex str
                if (reqWidth < seed.length) {
                      // so long we split
                    return seed.slice(seed.length - reqWidth);
                }
                if (reqWidth > seed.length) {
                      // so short we pad
                    return Array(1 + (reqWidth - seed.length))
                        .join('0') + seed;
                }
                return seed;
            };
            // BEGIN REDUNDANT
            if (!this.php_js) {
                this.php_js = {};
            }
              // END REDUNDANT
            if (!this.php_js.uniqidSeed) {
                // init seed with big random int
                this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
            }
            this.php_js.uniqidSeed++;

              // start with prefix, add current milliseconds hex string
            retId = prefix;
            retId += formatSeed(parseInt(new Date()
                .getTime() / 1000, 10), 8);
              // add seed hex string
            retId += formatSeed(this.php_js.uniqidSeed, 5);
            if (more_entropy) {
                // for more entropy we add a float lower to 10
                retId += (Math.random() * 10)
                  .toFixed(8)
                  .toString();
            }

            return retId;
        }
    }

    /**
     * Textarea and select clone() bug workaround | Spencer Tipping
     * Licensed under the terms of the MIT source code license
     * https://github.com/spencertipping/jquery.fix.clone/blob/master/jquery.fix.clone.js
     */

    if (!$().tm_clone) {
        $.fn.tm_clone = function() {
            var result = $.fn.clone.apply(this, arguments),
                my_textareas = this.find('textarea').add(this.filter('textarea')),
                result_textareas = result.find('textarea').add(result.filter('textarea')),
                my_selects = this.find('select').add(this.filter('select')),
                result_selects = result.find('select').add(result.filter('select'));
            for (var i = 0, l = my_textareas.length; i < l; ++i) {
                $(result_textareas[i]).val($(my_textareas[i]).val());
            }
            for (var i = 0, l = my_selects.length; i < l; ++i) {
                for (var j = 0, m = my_selects[i].options.length; j < m; ++j) {
                    if (my_selects[i].options[j].selected === true) {
                        result_selects[i].options[j].selected = true;
                    }
                }
            }
            return result;
        }
    }

    (function() {
        // based on easing equations from Robert Penner (http://www.robertpenner.com/easing)
        var baseEasings = {};
        $.each(["Quad", "Cubic", "Quart", "Quint", "Expo"], function(i, name) {
            baseEasings[name] = function(p) {
                return Math.pow(p, i + 2);
            };
        });
        $.extend(baseEasings, {
            Sine: function(p) {
                return 1 - Math.cos(p * Math.PI / 2);
            },
            Circ: function(p) {
                return 1 - Math.sqrt(1 - p * p);
            },
            Elastic: function(p) {
                return p === 0 || p === 1 ? p : -Math.pow(2, 8 * (p - 1)) * Math.sin(((p - 1) * 80 - 7.5) * Math.PI / 15);
            },
            Back: function(p) {
                return p * p * (3 * p - 2);
            },
            Bounce: function(p) {
                var pow2,
                    bounce = 4;

                while (p < ((pow2 = Math.pow(2, --bounce)) - 1) / 11) {}
                return 1 / Math.pow(4, 3 - bounce) - 7.5625 * Math.pow((pow2 * 3 - 2) / 22 - p, 2);
            }
        });
        $.each(baseEasings, function(name, easeIn) {
            $.easing["easeIn" + name] = easeIn;
            $.easing["easeOut" + name] = function(p) {
                return 1 - easeIn(1 - p);
            };
            $.easing["easeInOut" + name] = function(p) {
                return p < 0.5 ?
                    easeIn(p * 2) / 2 :
                    1 - easeIn(p * -2 + 2) / 2;
            };
        });
    })();

    if (!$().tm_getPageSize) {
        $.tm_getPageSize = function() {
            var e, t, pageHeight, pageWidth;
            if (window.innerHeight && window.scrollMaxY) {
                e = window.innerWidth + window.scrollMaxX;
                t = window.innerHeight + window.scrollMaxY;
            } else if (document.body.scrollHeight > document.body.offsetHeight) {
                e = document.body.scrollWidth;
                t = document.body.scrollHeight;
            } else {
                e = document.body.offsetWidth;
                t = document.body.offsetHeight;
            }
            var n, r;
            if (self.innerHeight) {
                if (document.documentElement.clientWidth) {
                    n = document.documentElement.clientWidth;
                } else {
                    n = self.innerWidth;
                }
                r = self.innerHeight
            } else if (document.documentElement && document.documentElement.clientHeight) {
                n = document.documentElement.clientWidth;
                r = document.documentElement.clientHeight;
            } else if (document.body) {
                n = document.body.clientWidth;
                r = document.body.clientHeight;
            }
            if (t < r) {
                pageHeight = r;
            } else {
                pageHeight = t;
            } if (e < n) {
                pageWidth = n;
            } else {
                pageWidth = e;
            }
            return new Array(pageWidth, pageHeight, n, r, e, t);

        }
    }

    if (!$().tm_getPageScroll) {
        $.tm_getPageScroll = function() {
            var e, t;
            if (self.pageYOffset) {
                t = self.pageYOffset;
                e = self.pageXOffset;
            } else if (document.documentElement && document.documentElement.scrollTop) {
                t = document.documentElement.scrollTop;
                e = document.documentElement.scrollLeft;
            } else if (document.body) {
                t = document.body.scrollTop;
                e = document.body.scrollLeft;
            }
            return new Array(e, t);

        }
    }

    if (!$().tm_floatbox) {
        $.fn.tm_floatbox = function(t) {
            function s(e) {
                if (o(e, n)) {
                    return n;
                } else {
                    return false;
                }
            }

            function f() {
                $(t.floatboxID).removeClass("animated appear");
                $(t.floatboxID).animate({
                    opacity: 0,
                    
                    top:"-=10%"
                    }, 400, function() {
                        $(t.floatboxID).remove();
                        
                    }
                );
                
                if (t.hideelements) $("embed, object, select").css({
                    visibility: "visible"
                });
                if (t.showoverlay == true) {
                            if (t._ovl) {
                                t._ovl.unbind();
                                t._ovl.remove();
                            }
                        }
                
                var _in = $.fn.tm_floatbox.instances.length;
                if (_in > 0) {
                    var _t = $.fn.tm_floatbox.instances[_in - 1];
                    if (t.id == _t.id) $.fn.tm_floatbox.instances.pop();
                }
            }

            function o(n, s) {
                if (s.length == 1) {
                    f();
                    if (t.hideelements) $("embed, object, select").css({
                        visibility: "hidden"
                    });
                    $(t.type).attr("id", t.id).addClass(t.classname).html(t.data).appendTo(n);
                    var _in = $.fn.tm_floatbox.instances.length;
                    if (_in > 0) {
                        var _t = $.fn.tm_floatbox.instances[_in - 1];
                        t.zIndex = _t.zIndex + 100;
                    }
                    $.fn.tm_floatbox.instances.push(t);
                    $(t.floatboxID).css({
                        width: t.width,
                        height: t.height
                    });
                    var o = $.tm_getPageSize();
                    var u = $.tm_getPageScroll();
                    var l = 0;
                    var c = parseInt(u[1] + (o[3] - $(t.floatboxID).height()) / 2);
                    var h = parseInt(u[0] + (o[2] - $(t.floatboxID).width()) / 2);
                    $(t.floatboxID).css({
                        top: l + "px",
                        left: h + "px",
                        "z-index": t.zIndex
                    });
                    r = l;
                    i = h;
                    n.cancelfunc = t.cancelfunc;
                    if (t.showoverlay == true) {
                        t._ovl = $('<div class="fl-overlay"></div>').css({
                            zIndex: (t.zIndex - 1),
                            opacity: .8
                        });
                        t._ovl.appendTo("body");
                        if (!t.ismodal) t._ovl.click(t.cancelfunc);
                    }
                    if (t.showfunc) {
                        t.showfunc.call();
                    }
                   
                    $(t.floatboxID).addClass("animated appear");
                    a();
                    $(window).on("scroll.tmfloatbox",doit);

                    return true;
                } else {
                    return false;
                }
            }

            function requestTick() {
                if(!ticking) {
                    if (t.refresh){
                        setTimeout(function() {
                            requestAnimationFrame(update);
                        }, t.refresh );
                    }else{
                        requestAnimationFrame(update);
                    }
                    
                    ticking = true;
                }
            }

            function update() {
                a();
                ticking = false;
            }

            function doit(){
                requestTick();
            }

            function u(n, r) {
                $(t.floatboxID).css({
                    top: n + "px",
                    left: r + "px",
                    opacity: 1
                });
            }

            function a() {
                var n = $.tm_getPageSize();
                var s = $.tm_getPageScroll();
                var o = parseInt(s[1] + (n[3] - $(t.floatboxID).height()) / 2);
                var a = parseInt(s[0] + (n[2] - $(t.floatboxID).width()) / 2);
                o = parseInt((o - r) / t.fps);
                a = parseInt((a - i) / t.fps);
                r += o;
                i += a;
                u(r, i);
            }

            t = jQuery.extend({
                id: "flasho",
                classname: "flasho",
                type: "div",
                data: "",
                width: "500px",
                height: "auto",
                refresh: false,
                fps: 4,
                hideelements: false,
                showoverlay: true,
                zIndex: 100100,
                ismodal: false,
                cancelfunc: f,
                showfunc: null
            }, t);
            t.floatboxID = "#" + t.id;
            t.type = "<" + t.type + ">";
            var n = this;
            var r = 0;
            var i = 0;
            var ticking = false;

            return s(this);
        }
        $.fn.tm_floatbox.instances = [];
        
    }

    if (!$().tmtabs) {
        $.fn.tmtabs = function() {
            var elements = this;
            
            if (elements.length==0){
                return;
            }

            return elements.each(function(){
                var t=$(this),
                    headers = t.find(".tm-tab-headers .tab-header");
                if (headers.length==0){
                    return;
                }
                var init_open=0,
                    last=false,
                    current="";
                headers.each(function(i,header){
                    
                    var id="."+$(header).attr("data-id");
                    $(header).data("tab",id);
                    t.find(id).hide().data("state","closed");
                    if (!init_open && $(header).is(".open")){
                        $(header).removeClass("closed open").addClass("open").data("state","open");
                        $(header).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-up");
                        t.find(id).data("state","open").show();
                        init_open=1;
                        current=id;
                        last=$(header);
                    }else{
                        $(header).removeClass("closed open").addClass("closed").data("state","closed");
                    }
                    
                    $(header).on("closetab.tmtabs",function(e){
                        var _tab=t.find($(this).data("tab"));
                        $(this).removeClass("closed open").addClass("closed");
                        $(this).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-down");
                        _tab.hide().removeClass("animated fadeInDown");
                    });

                    $(header).on("opentab.tmtabs",function(e){
                        $(this).removeClass("closed open").addClass("open");
                        $(this).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-up");
                        t.find($(this).data("tab")).show().removeClass("animated fadeInDown").addClass("animated fadeInDown");
                        current=$(this).data("tab");
                    });
                    
                    $(header).on("click.tmtabs",function(e){
                        e.preventDefault();
                        if (current==$(this).data("tab")){
                            return;
                        }
                        if (last){
                            $(last).trigger("closetab.tmtabs");
                        }
                        $(this).trigger("opentab.tmtabs");
                        last=$(this);
                    });

                });
            });
        };
    }
    
    if (!$().tmtoggle) {
        $.fn.tmtoggle = function() {
            var elements = this;
            
            if (elements.length==0){
                return;
            }

            return elements.each(function(){
                var t=$(this);
                if (!t.data('tm-toggle-init')){
                    t.data('tm-toggle-init',1);
                    var headers = t.find(".tm-toggle"),
                        wrap=t.find(".tm-collapse-wrap"),
                        wraps=$(".tm-collapse.accordion").find(".tm-toggle");
                    if (headers.length==0 || wrap.length==0){
                        return;
                    }

                    if (wrap.is(".closed")){
                        $(wrap).removeClass("closed open").addClass("closed").hide();
                        $(headers).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-down");
                    }else{
                        $(wrap).removeClass("closed open").addClass("open").show();
                        $(headers).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-up");
                    }

                    headers.each(function(i,header){
                                            
                        $(header).on("closewrap.tmtoggle",function(e){
                            if (t.is('.accordion') && $(wrap).is(".closed")){
                                return;
                            }                                            
                            $(wrap).removeClass("closed open").addClass("closed");
                            $(this).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-down");
                            $(wrap).removeClass("animated fadeInDown");
                            if (t.is('.accordion')){
                                $(wrap).hide();
                            }else{
                                $(wrap).animate({"height":"toggle"},100,function(){$(wrap).hide();});
                            }                        
                            $(window).trigger("tmlazy");
                        });

                        $(header).on("openwrap.tmtoggle",function(e){
                            if (t.is('.accordion')){
                                $(wraps).not($(this)).trigger("closewrap.tmtoggle");
                            }
                            $(wrap).removeClass("closed open").addClass("open");
                            $(this).find(".tm-arrow").removeClass("fa-angle-down fa-angle-up").addClass("fa-angle-up");
                            $(wrap).show().removeClass("animated fadeInDown").addClass("animated fadeInDown");
                            $(window).trigger("tmlazy");
                            if (t.is('.accordion') && !t.is_on_screen()){
                                $(window).scrollTo($(header));
                            }
                        });
                        
                        $(header).on("click.tmtoggle",function(e){
                            e.preventDefault();
                            if ($(wrap).is(".closed")){
                                $(this).trigger("openwrap.tmtoggle");                            
                            }else{
                                $(this).trigger("closewrap.tmtoggle");
                            }
                        });

                    });
                }
            });
        };
    }

    if (!$().tmpoplink) {
        $.fn.tmpoplink = function() {
            var elements = this;
            
            if (elements.length==0){
                return;
            }

            var floatbox_template= function(data) {
                var out = '';
                out = "<div class=\'header\'><h3>" + data.title + "<\/h3><\/div>" +
                    "<div id=\'" + data.id + "\' class=\'float_editbox\'>" +
                    data.html + "<\/div>" +
                    "<div class=\'footer\'><div class=\'inner\'><span class=\'tm-button button button-secondary button-large details_cancel\'>" +
                    tm_epo_js.i18n_close +
                    "<\/span><\/div><\/div>";
                return out;
            }

            return elements.each(function(){
                var t=$(this),
                    id=$(this).attr('href'),
                    title=$(this).attr('data-title')?$(this).attr('data-title'):tm_epo_js.i18n_addition_options,
                    html = $(id).html(),
                    $_html = floatbox_template({
                        "id": "temp_for_floatbox_insert",
                        "html": html,
                        "title": title
                    }),
                    clicked=false;

                t.on("click.tmpoplink",function(e){
                    e.preventDefault();
                    var _to = $("body").tm_floatbox({
                        "fps": 1,
                        "ismodal": false,
                        "refresh": 100,
                        "width": "80%",
                        "height": "80%",
                        "classname": "flasho tm_wrapper",
                        "data": $_html
                    });

                    $(".details_cancel").click(function() {
                        if (clicked){
                            return;
                        }
                        clicked=true;
                        if (_to){
                             clicked=false;
                            _to.cancelfunc();
                        }
                    });
                });
                

                
            });
        };
    }

})(jQuery);

// jQuery Mask Plugin v1.6.5
// github.com/igorescobar/jQuery-Mask-Plugin
/**
 * jquery.mask.js
 * @version: v1.6.5
 * @author: Igor Escobar
 *
 * Created by Igor Escobar on 2012-03-10. Please report any bug at http://blog.igorescobar.com
 *
 * Copyright (c) 2012 Igor Escobar http://blog.igorescobar.com
 *
 * The MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
(function(factory){if(typeof define==="function"&&define.amd)define(["jquery"],factory);else factory(window.jQuery||window.Zepto)})(function($){var Mask=function(el,mask,options){var jMask=this,old_value,regexMask;el=$(el);mask=typeof mask==="function"?mask(el.val(),undefined,el,options):mask;jMask.init=function(){options=options||{};jMask.byPassKeys=[9,16,17,18,36,37,38,39,40,91];jMask.translation={0:{pattern:/\d/},9:{pattern:/\d/,optional:true},"#":{pattern:/\d/,recursive:true},"A":{pattern:/[a-zA-Z0-9]/},
"S":{pattern:/[a-zA-Z]/}};jMask.translation=$.extend({},jMask.translation,options.translation);jMask=$.extend(true,{},jMask,options);regexMask=p.getRegexMask();el.each(function(){if(options.maxlength!==false)el.attr("maxlength",mask.length);if(options.placeholder)el.attr("placeholder",options.placeholder);el.attr("autocomplete","off");p.destroyEvents();p.events();var caret=p.getCaret();p.val(p.getMasked());p.setCaret(caret+p.getMaskCharactersBeforeCount(caret,true))})};var p={getCaret:function(){var sel,
pos=0,ctrl=el.get(0),dSel=document.selection,cSelStart=ctrl.selectionStart;if(dSel&&!~navigator.appVersion.indexOf("MSIE 10")){sel=dSel.createRange();sel.moveStart("character",el.is("input")?-el.val().length:-el.text().length);pos=sel.text.length}else if(cSelStart||cSelStart==="0")pos=cSelStart;return pos},setCaret:function(pos){if(el.is(":focus")){var range,ctrl=el.get(0);if(ctrl.setSelectionRange)ctrl.setSelectionRange(pos,pos);else if(ctrl.createTextRange){range=ctrl.createTextRange();range.collapse(true);
range.moveEnd("character",pos);range.moveStart("character",pos);range.select()}}},events:function(){el.on("keydown.mask",function(){old_value=p.val()});el.on("keyup.mask",p.behaviour);el.on("paste.mask drop.mask",function(){setTimeout(function(){el.keydown().keyup()},100)});el.on("change.mask",function(){el.data("changeCalled",true)});el.on("blur.mask",function(e){var el=$(e.target);if(el.prop("defaultValue")!==el.val()){el.prop("defaultValue",el.val());if(!el.data("changeCalled"))el.trigger("change")}el.data("changeCalled",
false)});el.on("focusout.mask",function(){if(options.clearIfNotMatch&&!regexMask.test(p.val()))p.val("")})},getRegexMask:function(){var maskChunks=[],translation,pattern,optional,recursive,oRecursive,r;for(var i=0;i<mask.length;i++){translation=jMask.translation[mask[i]];if(translation){pattern=translation.pattern.toString().replace(/.{1}$|^.{1}/g,"");optional=translation.optional;recursive=translation.recursive;if(recursive){maskChunks.push(mask[i]);oRecursive={digit:mask[i],pattern:pattern}}else maskChunks.push(!optional&&
!recursive?pattern:pattern+"?")}else maskChunks.push("\\"+mask[i])}r=maskChunks.join("");if(oRecursive)r=r.replace(new RegExp("("+oRecursive.digit+"(.*"+oRecursive.digit+")?)"),"($1)?").replace(new RegExp(oRecursive.digit,"g"),oRecursive.pattern);return new RegExp(r)},destroyEvents:function(){el.off("keydown.mask keyup.mask paste.mask drop.mask change.mask blur.mask focusout.mask").removeData("changeCalled")},val:function(v){var isInput=el.is("input");return arguments.length>0?isInput?el.val(v):el.text(v):
isInput?el.val():el.text()},getMaskCharactersBeforeCount:function(index,onCleanVal){for(var count=0,i=0,maskL=mask.length;i<maskL&&i<index;i++)if(!jMask.translation[mask.charAt(i)]){index=onCleanVal?index+1:index;count++}return count},determineCaretPos:function(originalCaretPos,oldLength,newLength,maskDif){var translation=jMask.translation[mask.charAt(Math.min(originalCaretPos-1,mask.length-1))];return!translation?p.determineCaretPos(originalCaretPos+1,oldLength,newLength,maskDif):Math.min(originalCaretPos+
newLength-oldLength-maskDif,newLength)},behaviour:function(e){e=e||window.event;var keyCode=e.keyCode||e.which;if($.inArray(keyCode,jMask.byPassKeys)===-1){var caretPos=p.getCaret(),currVal=p.val(),currValL=currVal.length,changeCaret=caretPos<currValL,newVal=p.getMasked(),newValL=newVal.length,maskDif=p.getMaskCharactersBeforeCount(newValL-1)-p.getMaskCharactersBeforeCount(currValL-1);if(newVal!==currVal)p.val(newVal);if(changeCaret&&!(keyCode===65&&e.ctrlKey)){if(!(keyCode===8||keyCode===46))caretPos=
p.determineCaretPos(caretPos,currValL,newValL,maskDif);p.setCaret(caretPos)}return p.callbacks(e)}},getMasked:function(skipMaskChars){var buf=[],value=p.val(),m=0,maskLen=mask.length,v=0,valLen=value.length,offset=1,addMethod="push",resetPos=-1,lastMaskChar,check;if(options.reverse){addMethod="unshift";offset=-1;lastMaskChar=0;m=maskLen-1;v=valLen-1;check=function(){return m>-1&&v>-1}}else{lastMaskChar=maskLen-1;check=function(){return m<maskLen&&v<valLen}}while(check()){var maskDigit=mask.charAt(m),
valDigit=value.charAt(v),translation=jMask.translation[maskDigit];if(translation){if(valDigit.match(translation.pattern)){buf[addMethod](valDigit);if(translation.recursive){if(resetPos===-1)resetPos=m;else if(m===lastMaskChar)m=resetPos-offset;if(lastMaskChar===resetPos)m-=offset}m+=offset}else if(translation.optional){m+=offset;v-=offset}v+=offset}else{if(!skipMaskChars)buf[addMethod](maskDigit);if(valDigit===maskDigit)v+=offset;m+=offset}}var lastMaskCharDigit=mask.charAt(lastMaskChar);if(maskLen===
valLen+1&&!jMask.translation[lastMaskCharDigit])buf.push(lastMaskCharDigit);return buf.join("")},callbacks:function(e){var val=p.val(),changed=p.val()!==old_value;if(changed===true)if(typeof options.onChange==="function")options.onChange(val,e,el,options);if(changed===true&&typeof options.onKeyPress==="function")options.onKeyPress(val,e,el,options);if(typeof options.onComplete==="function"&&val.length===mask.length)options.onComplete(val,e,el,options)}};jMask.remove=function(){var caret=p.getCaret(),
maskedCharacterCountBefore=p.getMaskCharactersBeforeCount(caret);p.destroyEvents();p.val(jMask.getCleanVal()).removeAttr("maxlength");p.setCaret(caret-maskedCharacterCountBefore)};jMask.getCleanVal=function(){return p.getMasked(true)};jMask.init()};$.fn.mask=function(mask,options){this.unmask();return this.each(function(){$(this).data("mask",new Mask(this,mask,options))})};$.fn.unmask=function(){return this.each(function(){try{$(this).data("mask").remove()}catch(e){}})};$.fn.cleanVal=function(){return $(this).data("mask").getCleanVal()};
$("*[data-mask]").each(function(){var input=$(this),options={},prefix="data-mask-";if(input.attr(prefix+"reverse")==="true")options.reverse=true;if(input.attr(prefix+"maxlength")==="false")options.maxlength=false;if(input.attr(prefix+"clearifnotmatch")==="true")options.clearIfNotMatch=true;if(input.attr(prefix+"placeholder")!==undefined)options.placeholder=input.attr(prefix+"placeholder");input.mask(input.attr("data-mask"),options)})});

/*! jQuery JSON plugin v2.5.1 | github.com/Krinkle/jquery-json */
!function($){"use strict";var escape=/["\\\x00-\x1f\x7f-\x9f]/g,meta={"\b":"\\b","  ":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},hasOwn=Object.prototype.hasOwnProperty;$.toJSON="object"==typeof JSON&&JSON.stringify?JSON.stringify:function(a){if(null===a)return"null";var b,c,d,e,f=$.type(a);if("undefined"===f)return void 0;if("number"===f||"boolean"===f)return String(a);if("string"===f)return $.quoteString(a);if("function"==typeof a.toJSON)return $.toJSON(a.toJSON());if("date"===f){var g=a.getUTCMonth()+1,h=a.getUTCDate(),i=a.getUTCFullYear(),j=a.getUTCHours(),k=a.getUTCMinutes(),l=a.getUTCSeconds(),m=a.getUTCMilliseconds();return 10>g&&(g="0"+g),10>h&&(h="0"+h),10>j&&(j="0"+j),10>k&&(k="0"+k),10>l&&(l="0"+l),100>m&&(m="0"+m),10>m&&(m="0"+m),'"'+i+"-"+g+"-"+h+"T"+j+":"+k+":"+l+"."+m+'Z"'}if(b=[],$.isArray(a)){for(c=0;c<a.length;c++)b.push($.toJSON(a[c])||"null");return"["+b.join(",")+"]"}if("object"==typeof a){for(c in a)if(hasOwn.call(a,c)){if(f=typeof c,"number"===f)d='"'+c+'"';else{if("string"!==f)continue;d=$.quoteString(c)}f=typeof a[c],"function"!==f&&"undefined"!==f&&(e=$.toJSON(a[c]),b.push(d+":"+e))}return"{"+b.join(",")+"}"}},$.evalJSON="object"==typeof JSON&&JSON.parse?JSON.parse:function(str){return eval("("+str+")")},$.secureEvalJSON="object"==typeof JSON&&JSON.parse?JSON.parse:function(str){var filtered=str.replace(/\\["\\\/bfnrtu]/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"");if(/^[\],:{}\s]*$/.test(filtered))return eval("("+str+")");throw new SyntaxError("Error parsing JSON, source is not valid.")},$.quoteString=function(a){return a.match(escape)?'"'+a.replace(escape,function(a){var b=meta[a];return"string"==typeof b?b:(b=a.charCodeAt(),"\\u00"+Math.floor(b/16).toString(16)+(b%16).toString(16))})+'"':'"'+a+'"'}}(jQuery);

/*! Lazy Load 1.9.3 - MIT license - Copyright 2010-2013 Mika Tuupola */
!function(a,b,c,d){var e=a(b);a.fn.lazyload=function(f){function g(){var b=0;i.each(function(){var c=a(this);if(!j.skip_invisible||c.is(":visible"))if(a.abovethetop(this,j)||a.leftofbegin(this,j));else if(a.belowthefold(this,j)||a.rightoffold(this,j)){if(++b>j.failure_limit)return!1}else c.trigger("appear"),b=0})}var h,i=this,j={threshold:0,failure_limit:0,event:"scroll",effect:"show",container:b,data_attribute:"original",skip_invisible:!0,appear:null,load:null,placeholder:"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"};return f&&(d!==f.failurelimit&&(f.failure_limit=f.failurelimit,delete f.failurelimit),d!==f.effectspeed&&(f.effect_speed=f.effectspeed,delete f.effectspeed),a.extend(j,f)),h=j.container===d||j.container===b?e:a(j.container),0===j.event.indexOf("scroll")&&h.bind(j.event,function(){return g()}),this.each(function(){var b=this,c=a(b);b.loaded=!1,(c.attr("src")===d||c.attr("src")===!1)&&c.is("img")&&c.attr("src",j.placeholder),c.one("appear",function(){if(!this.loaded){if(j.appear){var d=i.length;j.appear.call(b,d,j)}a("<img />").bind("load",function(){var d=c.attr("data-"+j.data_attribute);c.hide(),c.is("img")?c.attr("src",d):c.css("background-image","url('"+d+"')"),c[j.effect](j.effect_speed),b.loaded=!0;var e=a.grep(i,function(a){return!a.loaded});if(i=a(e),j.load){var f=i.length;j.load.call(b,f,j)}}).attr("src",c.attr("data-"+j.data_attribute))}}),0!==j.event.indexOf("scroll")&&c.bind(j.event,function(){b.loaded||c.trigger("appear")})}),e.bind("resize",function(){g()}),/(?:iphone|ipod|ipad).*os 5/gi.test(navigator.appVersion)&&e.bind("pageshow",function(b){b.originalEvent&&b.originalEvent.persisted&&i.each(function(){a(this).trigger("appear")})}),a(c).ready(function(){g()}),this},a.belowthefold=function(c,f){var g;return g=f.container===d||f.container===b?(b.innerHeight?b.innerHeight:e.height())+e.scrollTop():a(f.container).offset().top+a(f.container).height(),g<=a(c).offset().top-f.threshold},a.rightoffold=function(c,f){var g;return g=f.container===d||f.container===b?e.width()+e.scrollLeft():a(f.container).offset().left+a(f.container).width(),g<=a(c).offset().left-f.threshold},a.abovethetop=function(c,f){var g;return g=f.container===d||f.container===b?e.scrollTop():a(f.container).offset().top,g>=a(c).offset().top+f.threshold+a(c).height()},a.leftofbegin=function(c,f){var g;return g=f.container===d||f.container===b?e.scrollLeft():a(f.container).offset().left,g>=a(c).offset().left+f.threshold+a(c).width()},a.inviewport=function(b,c){return!(a.rightoffold(b,c)||a.leftofbegin(b,c)||a.belowthefold(b,c)||a.abovethetop(b,c))},a.extend(a.expr[":"],{"below-the-fold":function(b){return a.belowthefold(b,{threshold:0})},"above-the-top":function(b){return!a.belowthefold(b,{threshold:0})},"right-of-screen":function(b){return a.rightoffold(b,{threshold:0})},"left-of-screen":function(b){return!a.rightoffold(b,{threshold:0})},"in-viewport":function(b){return a.inviewport(b,{threshold:0})},"above-the-fold":function(b){return!a.belowthefold(b,{threshold:0})},"right-of-fold":function(b){return a.rightoffold(b,{threshold:0})},"left-of-fold":function(b){return!a.rightoffold(b,{threshold:0})}})}(jQuery,window,document);

/*
 *  Project: prettyCheckable
 *  Description: jQuery plugin to replace checkboxes and radios for custom images
 *  Author: Arthur Gouveia
 *  License: Licensed under the MIT License
 */
/*
 *  Project: prettyCheckable
 *  Description: jQuery plugin to replace checkboxes and radios for custom images
 *  Author: Arthur Gouveia
 *  License: Licensed under the MIT License
 */
/* global jQuery:true, ko:true */
;(function ( $, window, document, undefined ) {
    'use strict';

    var pluginName = 'prettyCheckable',
        dataPlugin = 'plugin_' + pluginName,
        defaults = {
            label: '',
            labelPosition: 'right',
            customClass: '',
            color: 'blue',
            nolabel:true
        };

    var addCheckableEvents = function (element) {
        if (window.ko) {
            $(element).on('change', function(e) {
                e.preventDefault();
                //only changes from knockout model
                if (e.originalEvent === undefined) {
                    var clickedParent = $(this).closest('.clearfix'),
                        fakeCheckable = $(clickedParent).find('a:first'),
                        isChecked = fakeCheckable.hasClass('checked');
                    if (isChecked === true) {
                        fakeCheckable.addClass('checked');
                    } else {
                        fakeCheckable.removeClass('checked');
                    }
                }
            });
        }

        element.find('a:first, label').on('touchstart click', function(e){
            e.preventDefault();
            var clickedParent = $(this).closest('.clearfix'),
                input = clickedParent.find('input'),
                fakeCheckable = clickedParent.find('a:first');

            if (fakeCheckable.hasClass('disabled') === true) {
                return;
            }

            if (input.prop('type') === 'radio') {
                $('input[name="' + input.attr('name') + '"]').each(function(index, el){
                    $(el).prop('checked', false).parent().find('a:first').removeClass('checked');
                });
            }

            if (window.ko) {
                ko.utils.triggerEvent(input[0], 'click');
            } else {
                if (input.prop('checked')) {
                    input.prop('checked', false).change();
                } else {
                    input.prop('checked', true).change();
                }
            }
            fakeCheckable.toggleClass('checked');
        });

        element.find('a:first').on('keyup', function(e){
            if (e.keyCode === 32) {
                $(this).click();
            }
        });
    };

    var Plugin = function ( element ) {
        this.element = element;
        this.options = $.extend( {}, defaults );
    };

    Plugin.prototype = {
        init: function ( options ) {
            $.extend( this.options, options );
            var el = $(this.element);
            el.parent().addClass('has-pretty-child');
            el.css('display', 'none');
            var classType = el.data('type') !== undefined ? el.data('type') : el.attr('type');
            var label = null,
                elLabelId = el.attr('id');
            if (elLabelId !== undefined && !this.options.nolabel) {
                var elLabel = $('label[for=' + elLabelId + ']');
                if (elLabel.length > 0) {
                    label = elLabel.text();
                    elLabel.remove();
                }
            }
            if (this.options.label === '') {
                this.options.label = label;
            }
            label = el.data('label') !== undefined ? el.data('label') : this.options.label;
            var labelPosition = el.data('labelposition') !== undefined ? 'label' + el.data('labelposition') : 'label' + this.options.labelPosition;
            var customClass = el.data('customclass') !== undefined ? el.data('customclass') : this.options.customClass;
            var color =  el.data('color') !== undefined ? el.data('color') : this.options.color;
            var disabled = el.prop('disabled') === true ? 'disabled' : '';
            var containerClasses = ['pretty' + classType, labelPosition, customClass, color].join(' ');
            el.wrap('<div class="clearfix ' + containerClasses + '"></div>').parent().html();
            var dom = [];
            var isChecked = el.prop('checked') ? 'checked' : '';
            if (labelPosition === 'labelright') {
                dom.push('<a href="#" class="' + isChecked + ' ' + disabled + '"></a>');
                if(!this.options.nolabel)dom.push('<label for="' + el.attr('id') + '">' + label + '</label>');
            } else {
                dom.push('<label for="' + el.attr('id') + '">' + label + '</label>');
                if(!this.options.nolabel)dom.push('<a href="#" class="' + isChecked + ' ' + disabled + '"></a>');
            }
            el.parent().append(dom.join('\n'));
            addCheckableEvents(el.parent());
        },

        check: function () {
            if ($(this.element).prop('type') === 'radio') {
                $('input[name="' + $(this.element).attr('name') + '"]').each(function(index, el){
                    $(el).prop('checked', false).attr('checked', false).parent().find('a:first').removeClass('checked');
                });
            }
            $(this.element).prop('checked', true).attr('checked', true).parent().find('a:first').addClass('checked');
        },
        uncheck: function () {
            $(this.element).prop('checked', false).attr('checked', false).parent().find('a:first').removeClass('checked');
        },
        enable: function () {
            $(this.element).removeAttr('disabled').parent().find('a:first').removeClass('disabled');
        },
        disable: function () {
            $(this.element).attr('disabled', 'disabled').parent().find('a:first').addClass('disabled');
        },
        destroy: function () {
            var el = $(this.element),
                clonedEl = el.clone(),
                label = null,
                elLabelId = el.attr('id');

            if (elLabelId !== undefined && !this.options.nolabel) {
                var elLabel = $('label[for=' + elLabelId + ']');
                if (elLabel.length > 0) {
                    elLabel.insertBefore(el.parent());
                }
            }
            clonedEl.removeAttr('style').insertAfter(elLabel);
            el.parent().remove();
        }
    };

    $.fn[ pluginName ] = function ( arg ) {
        var args, instance;
        if (!( this.data( dataPlugin ) instanceof Plugin )) {
            this.data( dataPlugin, new Plugin( this ) );
        }
        instance = this.data( dataPlugin );
        if (instance){
            instance.element = this;
            if (typeof arg === 'undefined' || typeof arg === 'object') {
                if ( typeof instance.init === 'function' ) {
                    instance.init( arg );
                }
            } else if ( typeof arg === 'string' && typeof instance[arg] === 'function' ) {
                args = Array.prototype.slice.call( arguments, 1 );
                return instance[arg].apply( instance, args );
            } else {
                $.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);
            }
        }
    };
}(jQuery, window, document));

/*
 *  Project: nouislider (http://refreshless.com/nouislider/)
 *  Description: noUiSlider is a range slider without bloat
 *  License: http://www.wtfpl.net/about/
 */
(function(){function c(a){return a.split("").reverse().join("")}function l(a,b){return a.substring(0,b.length)===b}function q(a,b,d){if((a[b]||a[d])&&a[b]===a[d])throw Error(b);}function m(a,b,d,e,n,h,w,k,A,H,D,g){w=g;var l,s=D="";h&&(g=h(g));if("number"!==typeof g||!isFinite(g))return!1;a&&0===parseFloat(g.toFixed(a))&&(g=0);0>g&&(l=!0,g=Math.abs(g));!1!==a&&(h=g,g=Math.pow(10,a),g=(Math.round(h*g)/g).toFixed(a));g=g.toString();-1!==g.indexOf(".")?(a=g.split("."),h=a[0],d&&(D=d+a[1])):h=g;b&&(h=
c(h).match(/.{1,3}/g),h=c(h.join(c(b))));l&&k&&(s+=k);e&&(s+=e);l&&A&&(s+=A);s=s+h+D;n&&(s+=n);H&&(s=H(s,w));return s}function u(a,b,d,c,e,h,w,k,A,H,D,g){var m;a="";D&&(g=D(g));if(!g||"string"!==typeof g)return!1;k&&l(g,k)&&(g=g.replace(k,""),m=!0);c&&l(g,c)&&(g=g.replace(c,""));A&&l(g,A)&&(g=g.replace(A,""),m=!0);if(c=e)c=g.slice(-1*e.length)===e;c&&(g=g.slice(0,-1*e.length));b&&(g=g.split(b).join(""));d&&(g=g.replace(d,"."));m&&(a+="-");a=(a+g).replace(/[^0-9\.\-.]/g,"");if(""===a)return!1;a=Number(a);
w&&(a=w(a));return"number"===typeof a&&isFinite(a)?a:!1}function a(a){var b,d,c,n={};for(b=0;b<e.length;b+=1)if(d=e[b],c=a[d],void 0===c)n[d]="negative"!==d||n.negativeBefore?"mark"===d&&"."!==n.thousand?".":!1:"-";else if("decimals"===d)if(0<=c&&8>c)n[d]=c;else throw Error(d);else if("encoder"===d||"decoder"===d||"edit"===d||"undo"===d)if("function"===typeof c)n[d]=c;else throw Error(d);else if("string"===typeof c)n[d]=c;else throw Error(d);q(n,"mark","thousand");q(n,"prefix","negative");q(n,"prefix",
"negativeBefore");return n}function b(a,b,d){var c,n=[];for(c=0;c<e.length;c+=1)n.push(a[e[c]]);n.push(d);return b.apply("",n)}function d(c){if(!(this instanceof d))return new d(c);"object"===typeof c&&(c=a(c),this.to=function(a){return b(c,m,a)},this.from=function(a){return b(c,u,a)})}var e="decimals thousand mark prefix postfix encoder decoder negativeBefore negative edit undo".split(" ");window.wNumb=d})();(function(c){function l(a){return a instanceof c||c.zepto&&c.zepto.isZ(a)}function q(a,b,d){var e=this,f=!1;this.changeHandler=function(a){var b=e.formatInstance.from(c(this).val());if(!1===b||isNaN(b))return c(this).val(e.lastSetValue),!1;e.changeHandlerMethod.call("",a,b)};this.el=!1;this.formatInstance=d;c.each(u,function(d,c){f=c.call(e,a,b);return!f});if(!f)throw new RangeError("(Link) Invalid Link.");}function m(a){this.items=[];this.elements=[];this.origin=a}var u=[function(a,b){if("string"===
typeof a&&0===a.indexOf("-inline-"))return this.method=b||"html",this.target=this.el=c(a.replace("-inline-","")||"<div/>"),!0},function(a){if("string"===typeof a&&0!==a.indexOf("-")){this.method="val";var b=document.createElement("input");b.name=a;b.type="hidden";this.target=this.el=c(b);return!0}},function(a){if("function"===typeof a)return this.target=!1,this.method=a,!0},function(a,b){if(l(a)&&!b)return a.is("input, select, textarea")?(this.method="val",this.target=a.on("change.liblink",this.changeHandler)):
(this.target=a,this.method="html"),!0},function(a,b){if(l(a)&&("function"===typeof b||"string"===typeof b&&a[b]))return this.method=b,this.target=a,!0}];q.prototype.set=function(a){var b=Array.prototype.slice.call(arguments).slice(1);this.lastSetValue=this.formatInstance.to(a);b.unshift(this.lastSetValue);("function"===typeof this.method?this.method:this.target[this.method]).apply(this.target,b)};m.prototype.push=function(a,b){this.items.push(a);b&&this.elements.push(b)};m.prototype.reconfirm=function(a){var b;
for(b=0;b<this.elements.length;b+=1)this.origin.LinkConfirm(a,this.elements[b])};m.prototype.remove=function(a){for(a=0;a<this.items.length;a+=1)this.items[a].target.off(".liblink");for(a=0;a<this.elements.length;a+=1)this.elements[a].remove()};m.prototype.change=function(a){if(this.origin.LinkIsEmitting)return!1;this.origin.LinkIsEmitting=!0;var b=Array.prototype.slice.call(arguments,1),d;b.unshift(a);for(d=0;d<this.items.length;d+=1)this.items[d].set.apply(this.items[d],b);this.origin.LinkIsEmitting=
!1};c.fn.Link=function(a){var b=this;if(!1===a)return b.each(function(){this.linkAPI&&(c.map(this.linkAPI,function(a){a.remove()}),delete this.linkAPI)});if(void 0===a)a=0;else if("string"!==typeof a)throw Error("Flag must be string.");return{to:function(d,e,f){return b.each(function(){var b=a;0===b&&(b=this.LinkDefaultFlag);this.linkAPI||(this.linkAPI={});this.linkAPI[b]||(this.linkAPI[b]=new m(this));var p=new q(d,e,f||this.LinkDefaultFormatter);p.target||(p.target=c(this));p.changeHandlerMethod=
this.LinkConfirm(b,p.el);this.linkAPI[b].push(p,p.el);this.LinkUpdate(b)})}}}})(window.jQuery||window.Zepto);(function(c){function l(a){return"number"===typeof a&&!isNaN(a)&&isFinite(a)}function q(a,b){return 100*b/(a[1]-a[0])}function m(a,b){for(var d=1;a>=b[d];)d+=1;return d}function u(a,b,d,c){this.xPct=[];this.xVal=[];this.xSteps=[c||!1];this.xNumSteps=[!1];this.snap=b;this.direction=d;for(var f in a)if(a.hasOwnProperty(f)){b=f;d=a[f];c=void 0;"number"===typeof d&&(d=[d]);if("[object Array]"!==Object.prototype.toString.call(d))throw Error("noUiSlider: 'range' contains invalid value.");c="min"===b?0:
"max"===b?100:parseFloat(b);if(!l(c)||!l(d[0]))throw Error("noUiSlider: 'range' value isn't numeric.");this.xPct.push(c);this.xVal.push(d[0]);c?this.xSteps.push(isNaN(d[1])?!1:d[1]):isNaN(d[1])||(this.xSteps[0]=d[1])}this.xNumSteps=this.xSteps.slice(0);for(f in this.xNumSteps)this.xNumSteps.hasOwnProperty(f)&&(a=Number(f),(b=this.xNumSteps[f])&&(this.xSteps[a]=q([this.xVal[a],this.xVal[a+1]],b)/(100/(this.xPct[a+1]-this.xPct[a]))))}u.prototype.getMargin=function(a){return 2===this.xPct.length?q(this.xVal,
a):!1};u.prototype.toStepping=function(a){var b=this.xVal,c=this.xPct;if(a>=b.slice(-1)[0])a=100;else{var e=m(a,b),f,l;f=b[e-1];l=b[e];b=c[e-1];c=c[e];f=[f,l];a=q(f,0>f[0]?a+Math.abs(f[0]):a-f[0]);a=b+a/(100/(c-b))}this.direction&&(a=100-a);return a};u.prototype.fromStepping=function(a){this.direction&&(a=100-a);var b;var c=this.xVal;b=this.xPct;if(100<=a)b=c.slice(-1)[0];else{var e=m(a,b),f,l;f=c[e-1];l=c[e];c=b[e-1];f=[f,l];b=100/(b[e]-c)*(a-c)*(f[1]-f[0])/100+f[0]}a=Math.pow(10,7);return Number((Math.round(b*
a)/a).toFixed(7))};u.prototype.getStep=function(a){this.direction&&(a=100-a);var b=this.xPct,c=this.xSteps,e=this.snap;if(100!==a){var f=m(a,b);e?(c=b[f-1],b=b[f],a=a-c>(b-c)/2?b:c):(c[f-1]?(e=b[f-1],c=c[f-1],b=Math.round((a-b[f-1])/c)*c,b=e+b):b=a,a=b)}this.direction&&(a=100-a);return a};u.prototype.getApplicableStep=function(a){var b=m(a,this.xPct);a=100===a?2:1;return[this.xNumSteps[b-2],this.xVal[b-a],this.xNumSteps[b-a]]};u.prototype.convert=function(a){return this.getStep(this.toStepping(a))};
c.noUiSlider={Spectrum:u}})(window.jQuery||window.Zepto);(function(c){function l(a){return"number"===typeof a&&!isNaN(a)&&isFinite(a)}function q(a,b){if(!l(b))throw Error("noUiSlider: 'step' is not numeric.");a.singleStep=b}function m(a,b){if("object"!==typeof b||c.isArray(b))throw Error("noUiSlider: 'range' is not an object.");if(void 0===b.min||void 0===b.max)throw Error("noUiSlider: Missing 'min' or 'max' in 'range'.");a.spectrum=new c.noUiSlider.Spectrum(b,a.snap,a.dir,a.singleStep)}function u(a,b){var d=b;b=c.isArray(d)?d:[d];if(!c.isArray(b)||!b.length||
2<b.length)throw Error("noUiSlider: 'start' option is incorrect.");a.handles=b.length;a.start=b}function a(a,b){a.snap=b;if("boolean"!==typeof b)throw Error("noUiSlider: 'snap' option must be a boolean.");}function b(a,b){a.animate=b;if("boolean"!==typeof b)throw Error("noUiSlider: 'animate' option must be a boolean.");}function d(a,b){if("lower"===b&&1===a.handles)a.connect=1;else if("upper"===b&&1===a.handles)a.connect=2;else if(!0===b&&2===a.handles)a.connect=3;else if(!1===b)a.connect=0;else throw Error("noUiSlider: 'connect' option doesn't match handle count.");
}function e(a,b){switch(b){case "horizontal":a.ort=0;break;case "vertical":a.ort=1;break;default:throw Error("noUiSlider: 'orientation' option is invalid.");}}function f(a,b){if(!l(b))throw Error("noUiSlider: 'margin' option must be numeric.");a.margin=a.spectrum.getMargin(b);if(!a.margin)throw Error("noUiSlider: 'margin' option is only supported on linear sliders.");}function z(a,b){if(!l(b))throw Error("noUiSlider: 'limit' option must be numeric.");a.limit=a.spectrum.getMargin(b);if(!a.limit)throw Error("noUiSlider: 'limit' option is only supported on linear sliders.");
}function p(a,b){switch(b){case "ltr":a.dir=0;break;case "rtl":a.dir=1;a.connect=[0,2,1,3][a.connect];break;default:throw Error("noUiSlider: 'direction' option was not recognized.");}}function r(a,b){if("string"!==typeof b)throw Error("noUiSlider: 'behaviour' must be a string containing options.");var c=0<=b.indexOf("tap"),d=0<=b.indexOf("drag"),h=0<=b.indexOf("fixed"),e=0<=b.indexOf("snap");a.events={tap:c||e,drag:d,fixed:h,snap:e}}function n(a,b){a.format=b;if("function"===typeof b.to&&"function"===
typeof b.from)return!0;throw Error("noUiSlider: 'format' requires 'to' and 'from' methods.");}var h={to:function(a){return a.toFixed(2)},from:Number};c.noUiSlider.testOptions=function(w){var k={margin:0,limit:0,animate:!0,format:h},A;A={step:{r:!1,t:q},start:{r:!0,t:u},connect:{r:!0,t:d},direction:{r:!0,t:p},snap:{r:!1,t:a},animate:{r:!1,t:b},range:{r:!0,t:m},orientation:{r:!1,t:e},margin:{r:!1,t:f},limit:{r:!1,t:z},behaviour:{r:!0,t:r},format:{r:!1,t:n}};w=c.extend({connect:!1,direction:"ltr",behaviour:"tap",
orientation:"horizontal"},w);c.each(A,function(a,b){if(void 0===w[a]){if(b.r)throw Error("noUiSlider: '"+a+"' is required.");return!0}b.t(k,w[a])});k.style=k.ort?"top":"left";return k}})(window.jQuery||window.Zepto);(function(c){function l(a){return Math.max(Math.min(a,100),0)}function q(a,b,c){a.addClass(b);setTimeout(function(){a.removeClass(b)},c)}function m(a,b){var d=c("<div><div/></div>").addClass(h[2]),e=["-lower","-upper"];a&&e.reverse();d.children().addClass(h[3]+" "+h[3]+e[b]);return d}function u(a,b,c){switch(a){case 1:b.addClass(h[7]);c[0].addClass(h[6]);break;case 3:c[1].addClass(h[6]);case 2:c[0].addClass(h[7]);case 0:b.addClass(h[6])}}function a(a,b,c){var d,e=[];for(d=0;d<a;d+=1)e.push(m(b,d).appendTo(c));
return e}function b(a,b,d){d.addClass([h[0],h[8+a],h[4+b]].join(" "));return c("<div/>").appendTo(d).addClass(h[1])}function d(d,k,e){function f(){return B[["width","height"][k.ort]]()}function m(a){var b,c=[v.val()];for(b=0;b<a.length;b+=1)v.trigger(a[b],c)}function g(a){return 1===a.length?a[0]:k.dir?a.reverse():a}function r(a){return function(b,c){v.val([a?null:c,a?c:null],!0)}}function s(a){var b=c.inArray(a,C);v[0].linkAPI&&v[0].linkAPI[a]&&v[0].linkAPI[a].change(F[b],t[b].children(),v)}function z(a,
b,c){var d=a[0]!==t[0][0]?1:0,e=y[0]+k.margin,f=y[1]-k.margin,g=y[0]+k.limit,n=y[1]-k.limit;1<t.length&&(b=d?Math.max(b,e):Math.min(b,f));!1!==c&&k.limit&&1<t.length&&(b=d?Math.min(b,g):Math.max(b,n));b=E.getStep(b);b=l(parseFloat(b.toFixed(7)));if(b===y[d])return!1;a.css(k.style,b+"%");a.is(":first-child")&&a.toggleClass(h[17],50<b);y[d]=b;F[d]=E.fromStepping(b);s(C[d]);return!0}function x(a,b,c,d){a=a.replace(/\s/g,".nui ")+".nui";return b.on(a,function(a){if(v.attr("disabled")||v.hasClass(h[14]))return!1;
a.preventDefault();var b=0===a.type.indexOf("touch"),e=0===a.type.indexOf("mouse"),f=0===a.type.indexOf("pointer"),g,J,I=a;0===a.type.indexOf("MSPointer")&&(f=!0);a.originalEvent&&(a=a.originalEvent);b&&(g=a.changedTouches[0].pageX,J=a.changedTouches[0].pageY);if(e||f)f||void 0!==window.pageXOffset||(window.pageXOffset=document.documentElement.scrollLeft,window.pageYOffset=document.documentElement.scrollTop),g=a.clientX+window.pageXOffset,J=a.clientY+window.pageYOffset;I.points=[g,J];I.cursor=e;a=
I;a.calcPoint=a.points[k.ort];c(a,d)})}function G(a,b){var c=b.handles||t,d,e=!1,e=100*(a.calcPoint-b.start)/f(),k=c[0][0]!==t[0][0]?1:0;var h=b.positions;d=e+h[0];e+=h[1];1<c.length?(0>d&&(e+=Math.abs(d)),100<e&&(d-=e-100),d=[l(d),l(e)]):d=[d,e];e=z(c[0],d[k],1===c.length);1<c.length&&(e=z(c[1],d[k?0:1],!1)||e);e&&m(["slide"])}function L(a){c("."+h[15]).removeClass(h[15]);a.cursor&&c("body").css("cursor","").off(".nui");p.off(".nui");v.removeClass(h[12]);m(["set","change"])}function K(a,b){1===b.handles.length&&
b.handles[0].children().addClass(h[15]);a.stopPropagation();x(n.move,p,G,{start:a.calcPoint,handles:b.handles,positions:[y[0],y[t.length-1]]});x(n.end,p,L,null);a.cursor&&(c("body").css("cursor",c(a.target).css("cursor")),1<t.length&&v.addClass(h[12]),c("body").on("selectstart.nui",!1))}function M(a){var b=a.calcPoint,d=0;a.stopPropagation();c.each(t,function(){d+=this.offset()[k.style]});d=b<d/2||1===t.length?0:1;b-=B.offset()[k.style];b=100*b/f();k.events.snap||q(v,h[14],300);z(t[d],b);m(["slide",
"set","change"]);k.events.snap&&K(a,{handles:[t[d]]})}var v=c(d),y=[-1,-1],B,t,E=k.spectrum,F=[],C=["lower","upper"].slice(0,k.handles);k.dir&&C.reverse();d.LinkUpdate=s;d.LinkConfirm=function(a,b){var d=c.inArray(a,C);b&&b.appendTo(t[d].children());k.dir&&(d=1===d?0:1);return r(d)};d.LinkDefaultFormatter=k.format;d.LinkDefaultFlag="lower";d.reappend=function(){var a,b;for(a=0;a<C.length;a+=1)this.linkAPI&&this.linkAPI[b=C[a]]&&this.linkAPI[b].reconfirm(b)};if(v.hasClass(h[0]))throw Error("Slider was already initialized.");
B=b(k.dir,k.ort,v);t=a(k.handles,k.dir,B);u(k.connect,v,t);(function(a){var b;if(!a.fixed)for(b=0;b<t.length;b+=1)x(n.start,t[b].children(),K,{handles:[t[b]]});a.tap&&x(n.start,B,M,{handles:t});a.drag&&(b=B.find("."+h[7]).addClass(h[10]),a.fixed&&(b=b.add(B.children().not(b).children())),x(n.start,b,K,{handles:t}))})(k.events);d.vSet=function(a){if(v[0].LinkIsEmitting)return this;var b;a=c.isArray(a)?a:[a];k.dir&&1<k.handles&&a.reverse();k.animate&&-1!==y[0]&&q(v,h[14],300);b=1<t.length?3:1;1===a.length&&
(b=1);var d,e,f;k.limit&&(b+=1);for(d=0;d<b;d+=1)e=d%2,f=a[e],null!==f&&!1!==f&&("number"===typeof f&&(f=String(f)),f=k.format.from(f),(!1===f||isNaN(f)||!1===z(t[e],E.toStepping(f),d===3-k.dir))&&s(C[e]));m(["set"]);return this};d.vGet=function(){var a,b=[];for(a=0;a<k.handles;a+=1)b[a]=k.format.to(F[a]);return g(b)};d.destroy=function(){c(this).off(".nui").removeClass(h.join(" ")).empty();delete this.LinkUpdate;delete this.LinkConfirm;delete this.LinkDefaultFormatter;delete this.LinkDefaultFlag;
delete this.reappend;delete this.vGet;delete this.vSet;delete this.getCurrentStep;delete this.getInfo;delete this.destroy;return e};d.getCurrentStep=function(){var a=c.map(y,function(a,b){var c=E.getApplicableStep(a);return[[F[b]-c[2]>=c[1]?c[2]:c[0],c[2]]]});return g(a)};d.getInfo=function(){return[E,k.style,k.ort]};v.val(k.start)}function e(a){if(!this.length)throw Error("noUiSlider: Can't initialize slider on empty selection.");var b=c.noUiSlider.testOptions(a,this);return this.each(function(){d(this,
b,a)})}function f(a){return this.each(function(){if(this.destroy){var b=c(this).val(),d=this.destroy(),e=c.extend({},d,a);c(this).noUiSlider(e);this.reappend();d.start===e.start&&c(this).val(b)}else c(this).noUiSlider(a)})}function z(){return this[0][arguments.length?"vSet":"vGet"].apply(this[0],arguments)}var p=c(document),r=c.fn.val,n=window.navigator.pointerEnabled?{start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled?{start:"MSPointerDown",move:"MSPointerMove",
end:"MSPointerUp"}:{start:"mousedown touchstart",move:"mousemove touchmove",end:"mouseup touchend"},h="noUi-target noUi-base noUi-origin noUi-handle noUi-horizontal noUi-vertical noUi-background noUi-connect noUi-ltr noUi-rtl noUi-dragable  noUi-state-drag  noUi-state-tap noUi-active  noUi-stacking".split(" ");c.fn.val=function(){var a=arguments,b=c(this[0]);return arguments.length?this.each(function(){(c(this).hasClass(h[0])?z:r).apply(c(this),a)}):(b.hasClass(h[0])?z:r).call(b)};c.fn.noUiSlider=
function(a,b){return(b?f:e).call(this,a)}})(window.jQuery||window.Zepto);(function(c){function l(a){return c.grep(a,function(b,d){return d===c.inArray(b,a)})}function q(a,b,d,e){if("range"===b||"steps"===b)return a.xVal;if("count"===b){b=100/(d-1);var f,l=0;for(d=[];100>=(f=l++*b);)d.push(f);b="positions"}if("positions"===b)return c.map(d,function(b){return a.fromStepping(e?a.getStep(b):b)});if("values"===b)return e?c.map(d,function(b){return a.fromStepping(a.getStep(a.toStepping(b)))}):d}function m(a,b,d,e){var f=a.direction,m={},p=a.xVal[0],r=a.xVal[a.xVal.length-1],
n=!1,h=!1,w=0;a.direction=0;e=l(e.slice().sort(function(a,b){return a-b}));e[0]!==p&&(e.unshift(p),n=!0);e[e.length-1]!==r&&(e.push(r),h=!0);c.each(e,function(f){var l,p,r,g=e[f],q=e[f+1],s,u,x,G;"steps"===d&&(l=a.xNumSteps[f]);l||(l=q-g);if(!1!==g&&void 0!==q)for(p=g;p<=q;p+=l){s=a.toStepping(p);r=s-w;x=r/b;x=Math.round(x);G=r/x;for(r=1;r<=x;r+=1)u=w+r*G,m[u.toFixed(5)]=["x",0];x=-1<c.inArray(p,e)?1:"steps"===d?2:0;f||!n||g||(x=0);p===q&&h||(m[s.toFixed(5)]=[p,x]);w=s}});a.direction=f;return m}function u(a,
b,d,e,f){function l(b,c,d){c='class="'+c+" "+c+"-"+p+" "+c;var e=d[1];d=["-normal","-large","-sub"][e&&f?f(d[0],e):e];return c+d+'" style="'+a+": "+b+'%"'}var p=["horizontal","vertical"][b],m=c("<div/>");m.addClass("noUi-pips noUi-pips-"+p);c.each(e,function(a,b){d&&(a=100-a);m.append("<div "+l(a,"noUi-marker",b)+"></div>");b[1]&&m.append("<div "+l(a,"noUi-value",b)+">"+Math.round(b[0])+"</div>")});return m}c.fn.noUiSlider_pips=function(a){var b=a.mode,d=a.density||1,e=a.filter||!1,f=a.values||!1,
l=a.stepped||!1;return this.each(function(){var a=this.getInfo(),r=q(a[0],b,f,l),r=m(a[0],d,b,r);return c(this).append(u(a[1],a[2],a[0].direction,r,e))})}})(window.jQuery||window.Zepto);

/**
 * Copyright (c) 2007-2014 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
 * Licensed under MIT
 * @author Ariel Flesler
 * @version 1.4.13
 */
;(function(k){'use strict';k(['jquery'],function($){var j=$.scrollTo=function(a,b,c){return $(window).scrollTo(a,b,c)};j.defaults={axis:'xy',duration:parseFloat($.fn.jquery)>=1.3?0:1,limit:!0};j.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return/webkit/i.test(navigator.userAgent)||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollTo=function(f,g,h){if(typeof g=='object'){h=g;g=0}if(typeof h=='function')h={onAfter:h};if(f=='max')f=9e9;h=$.extend({},j.defaults,h);g=g||h.duration;h.queue=h.queue&&h.axis.length>1;if(h.queue)g/=2;h.offset=both(h.offset);h.over=both(h.over);return this._scrollable().each(function(){if(f==null)return;var d=this,$elem=$(d),targ=f,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)){targ=both(targ);break}targ=win?$(targ):$(targ,this);if(!targ.length)return;case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}var e=$.isFunction(h.offset)&&h.offset(d,targ)||h.offset;$.each(h.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=j.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(h.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=e[pos]||0;if(h.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*h.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(h.limit&&/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&h.queue){if(old!=attr[key])animate(h.onAfterFirst);delete attr[key]}});animate(h.onAfter);function animate(a){$elem.animate(attr,g,h.easing,a&&function(){a.call(this,targ,h)})}}).end()};j.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return $.isFunction(a)||typeof a=='object'?a:{top:a,left:a}}return j})}(typeof define==='function'&&define.amd?define:function(a,b){if(typeof module!=='undefined'&&module.exports){module.exports=b(require('jquery'))}else{b(jQuery)}}));

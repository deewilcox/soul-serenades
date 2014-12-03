(function($) {
    "use strict";
    
    function tm_set_range_pickers(){
        $('.tm-range-picker').each(function(i,el){
            var $decimals=$(el).attr('data-step').split(".");
            if ($decimals.length==1){
                $decimals=0;
            }else{
                $decimals=$decimals[1].length;
            }
            
            var $tmfid=$('#'+$(el).attr('data-field-id'));

            $(el).noUiSlider({
                start: parseFloat($(el).attr('data-start')),
                step: parseFloat($(el).attr('data-step')),
                connect: 'lower',            
                // Configure tapping, or make the selected range dragable.
                behaviour: 'snap',            
                // Full number format support.
                format: wNumb({
                    mark: ".",
                    decimals: $decimals,
                    thousand: "",
                }),            
                // Support for non-linear ranges by adding intervals.
                range: {
                    'min': parseFloat($(el).attr('data-min')),
                    'max': parseFloat($(el).attr('data-max'))
                }
            }).on("slide",function(){
                $tmh.attr('title',$tmfid.val());
                $tmh.trigger('tmmovetooltip');
            }).on("set",function(){
                $tmh.attr('title',$tmfid.val());
            });
            var $tmh=$(el).find('.noUi-handle-lower');
            $tmh.attr('title',$(el).attr('data-start'));
            $.tm_tooltip($tmh);

            if ($(el).attr('data-pips')=="yes"){
                $(el).noUiSlider_pips({
                    mode: 'count',
                    values: 5,
                    density: 2,
                    stepped: true
                });
            }
            $(el).Link('lower').to($tmfid);
            
        });
        
    }

    function tm_set_datepicker(){
        if (!$.datepicker){
            return;
        }

        var inputIds = $('input').map(function() {
            return this.id;
        }).get().join(' ');

        var update_date_fields = function(input, inst){
            var id = $(input).attr("id"),
                day = $('#' + id + '_day'),
                month = $('#' + id + '_month'),
                year = $('#' + id + '_year');
                    
            day.val(inst.selectedDay);
            month.val(inst.selectedMonth + 1);
            year.val(inst.selectedYear);
        };

        $( ".tm-epo-datepicker" ).each(function(i,el){
            var startDate=parseInt($(this).attr('data-start-year')),
                endDate=parseInt($(this).attr('data-end-year')),
                format=$(this).attr('data-date-format'),
                show=$(this).attr('data-date-showon');

            $(el).datepicker({
                monthNames: tm_epo_js.monthNames,
                monthNamesShort: tm_epo_js.monthNamesShort,
                dayNames: tm_epo_js.dayNames,
                dayNamesShort: tm_epo_js.dayNamesShort,
                dayNamesMin: tm_epo_js.dayNamesMin,
                isRTL: tm_epo_js.isRTL,

                showOn: show,
                buttonText:"",
                showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
                dateFormat: format,
                minDate: new Date(startDate, 1 - 1, 1),
                maxDate: new Date(endDate, 12 - 1, 31),
                onSelect: function (dateText, inst) {
                   update_date_fields(this, inst);
                },
                beforeShow: function(input, inst) {
                    $('#ui-datepicker-div').removeClass(inputIds);
                    $('#ui-datepicker-div').addClass(this.id+ ' tm-epo-skin');
                    $("body").addClass("tm-static");
                    $(el).prop("readonly",true);
                },
                onClose: function(dateText, inst) {
                    $("body").removeClass("tm-static");
                    $(el).trigger("change");
                    $(el).prop("readonly",false);
                }
            }).on('change.tmdate', function(e){
                var id='#' + $(this).attr("id"),
                    input=$(this),
                    format=input.attr('data-date-format'),
                    date = input.datepicker('getDate'),
                    day='',month='',year='',
                    day_field=$(id + '_day'),
                    month_field=$(id + '_month'),
                    year_field=$(id + '_year');

                if (date){
                    day  = date.getDate();
                    month = date.getMonth() + 1;
                    year =  date.getFullYear();
                }
                day_field.val(day);
                month_field.val(month);
                year_field.val(year);
                
            });
        });
        
        $('.tmcp-date-select').on('change.cpf',function(e){
            var id='#' + $(this).attr("data-tm-date"),
                input=$(id),
                format=input.attr('data-date-format'),
                day=$(id + '_day').val(),
                month=$(id + '_month').val(),
                year=$(id + '_year').val(),
                dateFormat = $.datepicker.formatDate(format, new Date( year, month-1, day));
            if (day>0 && month>0 && year>0){
                input.datepicker( "setDate", dateFormat );
                input.trigger("change");
            }else{
                input.val("");
                input.trigger("change.cpf");
            }            
        });       

        $(window).on("resizestart",function() {            
            var field = $(document.activeElement);
            if (field.is('.hasDatepicker')) {
                field.data("resizestarted",true);
                if ($(window).width()<768){
                    field.data("resizewidth",true);
                    return;
                }
                field.datepicker('hide');                
            }
        });
        $(window).on("resizestop",function() {            
            var field = $(document.activeElement);
            if (field.is('.hasDatepicker') && field.data("resizestarted")) {
                if (field.data("resizewidth")){
                    field.datepicker('hide');
                }
                field.datepicker('show');                
            }
            field.data("resizestarted",false);
            field.data("resizewidth",false);
        });

    };

    if (!$().tmsectionpoplink) {
        $.fn.tmsectionpoplink = function() {
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
                    id=$(this).attr('data-sectionid'),
                    title=$(this).attr('data-title')?$(this).attr('data-title'):tm_epo_js.i18n_addition_options,
                    section = $('div[data-uniqid="'+id+'"]');
                    var html_clone=section.tm_clone();                    
                    
                    var $_html = floatbox_template({
                        "id": "temp_for_floatbox_insert",
                        "html": '',
                        "title": title
                    }),
                    clicked=false;
                    var _ovl = $('<div class="fl-overlay"></div>').css({
                        zIndex: (t.zIndex - 1),
                        opacity: .8
                    });

                var cancelfunc=function(){
                    _ovl.unbind().remove();
                    $('#tm-section-pop-up').find('.header').remove();
                    $('#tm-section-pop-up').find('.footer').remove();
                    section.unwrap();
                    section.unwrap();
                    section.find('.tm-section-link').show();
                    section.find('.tm-section-pop').hide();

                }

                t.on("click.tmsectionpoplink",function(e){
                    e.preventDefault();
                    clicked=false;
                    _ovl.appendTo("body").click(cancelfunc);
                    
                    section.wrap('<div id="tm-section-pop-up" class="flasho tm_wrapper tm-section-pop-up single animated appear">');
                    section.wrap('<div class="float_editbox" id="temp_for_floatbox_insert">');
                    $('#tm-section-pop-up').prepend("<div class=\'header\'><h3>" + title + "<\/h3><\/div>");
                    $('#tm-section-pop-up').append("<div class=\'footer\'><div class=\'inner\'><span class=\'tm-button button button-secondary button-large details_cancel\'>" + tm_epo_js.i18n_close + "<\/span><\/div><\/div>");
                    section.find('.tm-section-link').hide();
                    section.find('.tm-section-pop').show();

                    $('#tm-section-pop-up').find(".details_cancel").click(function() {
                        if (clicked){
                            return;
                        }
                        clicked=true;
                        cancelfunc();                        
                    });                    
                });
                

                
            });
        };
    }

    if (!$().cpfdependson) {
        
        $.fn.cpfdependson = function(fields, toggle, what) {
            var elements    = this,
                matches     = 0;
            
            if (!toggle){
                toggle="show";
            }
            if (!what){
                what="all";
            }

            if (elements.length==0){
                return;
            }
            if (!typeof fields =="object"){
                return;
            }
            
            $.each(fields,function(i,field){
                if (!typeof fields =="object"){
                    return true;
                }
                var element=get_element_from_field(field.element);
                if (element && !$(element).data('tmhaslogicevents')){
                    var _events="change.cpflogic";
                    if ($(element).is(":text") || $(element).is("textarea")){
                        _events="change.cpflogic keyup.cpflogic";
                    }
                    $(element).off(_events).on(_events,function(e){
                        run_cpfdependson();
                        
                    });
                    $(element).data('tmhaslogicevents',1);
                }
                matches++;
            });
            
            elements.each(function(i,el){                       
                $(this).data("matches",matches);
                $(this).data("toggle",toggle);
                $(this).data("what",what);
                $(this).data("fields",fields);
                var show=false;
                switch (toggle){
                    case "show":
                        show=false;
                    break;
                    case "hide":
                        show=true;
                    break;
                }
                if (show){
                    $(this).show();
                }else{
                    $(this).hide();
                }                
                $(this).data('isactive',show);
            });
            elements.addClass('iscpfdependson').data('iscpfdependson',1);
            return elements.each(function(){
                $(this).addClass("is-epo-depend");
            });
        };
    }

    function run_cpfdependson(obj){
        if (!$(obj).length){
            obj="body";
        }
        var iscpfdependson = $(obj).find('.iscpfdependson');
        iscpfdependson.each(function(i,elements){
            $(elements).each(function(j,el){                
                tm_check_rules($(el));
            });            
        });
        iscpfdependson.each(function(i,elements){
            $(elements).each(function(j,el){                
                tm_check_rules($(el),'cpflogic');                
            });            
        });
        $(window).trigger("tmlazy");
    }

    function tm_check_rules(o,theevent){
        o.each(function(theindex,theelement){                    
            var matches = $(this).data("matches"),
                toggle  = $(this).data("toggle"),
                what    = $(this).data("what"),
                fields  = $(this).data("fields"),
                checked = 0,
                show    = false;

            switch (toggle){
                case "show":
                    show=false;
                break;
                case "hide":
                    show=true;
                break;
            }

            $.each(fields,function(i,field){
                var fia=true;
                if (theevent=='cpflogic'){
                    fia=field_is_active($(field.element));
                }
                if (fia && tm_check_field_match(field)){
                    checked++;
                }
            });

            if (what=="all"){
                if (matches==checked){
                    show=!show;
                }
            }else{
                if (checked>0){
                    show=!show;
                }

            }
            if (show){
                $(this).show();
            }else{
                $(this).hide();
            }
            $(this).data('isactive',show);
        });
    }

    function tm_check_field_match(f){
        var element     = $(f.element),
            operator    = f.operator,
            value       = f.value,
            val,
            _class      = element.attr("class").split(' ')
            .map(function(cls) {
                if (cls.indexOf("cpf-type-", 0) !== -1) {
                    return cls;
                }
            })
            .filter(function(v, k, el) {
                if (v !== null && v !== undefined) {
                    return v;
                }
            });
                
        if (_class.length>0){
            _class=_class[0];
            switch (_class){
                case "cpf-type-radio" :
                    var radio           = element.find(".tm-epo-field.tmcp-radio"),
                        radio_checked   = element.find(".tm-epo-field.tmcp-radio:checked");

                    if (operator=='is' || operator=='isnot'){
                        if (radio_checked.length==0){
                            return false;
                        }
                        var eq=radio.index(radio_checked),
                            builder_addition="_"+eq;

                        builder_addition=builder_addition.length;                        
                        val=element.find(".tm-epo-field.tmcp-radio:checked").val();
                        if(val){
                            val=val.slice(0,-builder_addition); 
                        }
                    }else if (operator=='isnotempty'){
                        return radio_checked.length>0
                    }else if (operator=='isempty'){
                        return radio_checked.length==0
                    }
                    break;
                case "cpf-type-checkbox" :
                    var checkbox            = element.find(".tm-epo-field.tmcp-checkbox"),
                        checkbox_checked    = element.find(".tm-epo-field.tmcp-checkbox:checked");

                    if (operator=='is' || operator=='isnot'){
                        if (checkbox_checked.length==0){
                            return false;
                        }
                        var ret=false;
                        checkbox_checked.each(function(i,el){
                            var eq                  = checkbox.index($(el)),
                                builder_addition    = "_"+eq;

                            builder_addition=builder_addition.length;
                            val=$(el).val();
                            if(val){
                                val=val.slice(0,-builder_addition); 
                            }
                            if (tm_check_match(val,value,operator)){
                                ret=true;
                            }
                        });
                        return ret;
                    }else if (operator=='isnotempty'){
                        return checkbox_checked.length>0
                    }else if (operator=='isempty'){
                        return checkbox_checked.length==0
                    } 
                    break;
                case "cpf-type-select" :
                    var select = element.find(".tm-epo-field.tmcp-select"),
                        options = element.find(".tm-epo-field.tmcp-select").children('option'),
                        selected = element.find(".tm-epo-field.tmcp-select").children('option:selected');
                    var eq=options.index(selected),
                        builder_addition="_"+eq;

                    builder_addition=builder_addition.length;
                    val=element.find(".tm-epo-field.tmcp-select").val();
                    if(val){
                        val=val.slice(0,-builder_addition); 
                    }

                    break;
                case "cpf-type-textarea" :
                    val=element.find(".tm-epo-field.tmcp-textarea").val();

                    break;
                case "cpf-type-textfield" :
                    val=element.find(".tm-epo-field.tmcp-textfield").val();
                    break;
                }
            return tm_check_match(val,value,operator);

        }else{
            return false;
        }

        return false;
                    
    }

    function tm_check_match(val1, val2, operator){
        if (val1!=null && val1!=null){
            //return false;
            val1=encodeURIComponent(val1);
            val2=encodeURIComponent(decodeURIComponent(val2));//backwards compatible
                    
            val1 = val1 ? val1.toLowerCase() : "";
            val2 = val2 ? val2.toLowerCase() : "";
        }
        switch(operator){
        case "is" :
            return (val1!=null && val1 == val2);
            break;

        case "isnot" :
            return (val1!=null && val1 != val2);
            break;

        case "isempty" :
            return !( (val1 != undefined && val1!='') );
            break;

        case "isnotempty" :
            return ( (val1 != undefined && val1!='') );
            break;

        }
        return false;
    }    

    function field_is_active(field){
        var hide_element;
        if (!$(field).is('.cpf_hide_element')){
            hide_element=$(field).closest('.cpf_hide_element');
        }else{
            hide_element=$(field);
        }

        if ($(hide_element).data('isactive')!==false && $(hide_element).closest('.cpf-section').data('isactive')!==false){
            $(field).prop('disabled',false);
            return true;
        }
        if (!$(field).is('.cpf_hide_element')){
            $(field).prop('disabled',true);
        }
        return false;
    }

    function get_element_from_field(element){

        if ($(element).length==0){
            return;
        }
        
        var _class=element.attr("class").split(' ')
            .map(function(cls) {
                if (cls.indexOf("cpf-type-", 0) !== -1) {
                    return cls;
                }
            })
            .filter(function(v, k, el) {
                if (v !== null && v !== undefined) {
                    return v;
                }
            });

        if (_class.length>0){
            _class=_class[0];
            
            switch (_class){
                case "cpf-type-radio" :
                    return element.find(".tm-epo-field.tmcp-radio");
                    break;
                case "cpf-type-checkbox" :
                    return element.find(".tm-epo-field.tmcp-checkbox");
                    break;
                case "cpf-type-select" :
                    return element.find(".tm-epo-field.tmcp-select");
                    break;
                case "cpf-type-textarea" :
                    return element.find(".tm-epo-field.tmcp-textarea");
                    break;
                case "cpf-type-textfield" :
                    return element.find(".tm-epo-field.tmcp-textfield");
                    break;
                case "cpf-type-date" :
                    return element.find(".tm-epo-field.tmcp-date");
                    break;
            }
            return;
        }
        return;
    }

    var validate_logic=function(l){
        return (typeof l =="object") && ("toggle" in l) && ("what" in l) && ("rules" in l) && (l.rules.length>0);
    }

    var cpf_section_logic=function(obj){
        $(obj).find(".cpf-section").each(function(index,el){
            var id          = $(el).data("uniqid"),
                logic       = $(el).data("logic"),
                haslogic    = parseInt($(el).data("haslogic"));

            if (haslogic==1 && validate_logic(logic)){
                var fields=[];
                $.each(logic.rules,function(i,rule){
                    var section     = rule.section,
                        element     = rule.element,
                        operator    = rule.operator,
                        value       = rule.value,
                        obj_section = $(obj).find('.cpf-section[data-uniqid="'+section+'"]'),
                        obj_element = obj_section.find(".cpf_hide_element").eq(element);

                    fields.push({
                        "element":obj_element,
                        "operator":operator,
                        "value":value
                    });
                });
                $(el).cpfdependson(fields,logic.toggle,logic.what);
            }
        });
        run_cpfdependson(obj);
    }

    var cpf_element_logic=function(obj){
        var root_element=$(obj);
        root_element.find(".cpf_hide_element").each(function(index,el){
            var current_element = $(el),
                id              = current_element.data("uniqid"),
                logic           = current_element.data("logic"),
                haslogic        = parseInt(current_element.data("haslogic"));

            if (haslogic==1 && validate_logic(logic)){
                var fields=[];
                $.each(logic.rules,function(i,rule){
                    var section     = rule.section,
                        element     = rule.element,
                        operator    = rule.operator,
                        value       = rule.value,
                        obj_section = root_element.find('.cpf-section[data-uniqid="'+section+'"]'),
                        obj_element = obj_section.find(".cpf_hide_element").eq(element);
                    fields.push({
                        "element":obj_element,
                        "operator":operator,
                        "value":value
                    });
                });
                current_element.cpfdependson(fields,logic.toggle,logic.what);                
            }
        });
        run_cpfdependson(obj);
    }

    function tm_set_url_fields(){
        $(document).on("click.cpfurl change.cpfurl tmredirect", ".use_url_containter .tmcp-radio, .use_url_containter .tmcp-radio+label", function(e) {
            var data_url=$(this).attr("data-url");
            if (data_url){
                if (window.location!=data_url){
                    e.preventDefault();                
                    window.location=data_url;
                }
            }
        });
        $(document).on("change.cpfurl tmredirect", ".use_url_containter .tmcp-select", function(e) {
            var selected=$(this).children('option:selected'),
                data_url=selected.attr("data-url");
            if (data_url){
                if (window.location!=data_url){
                    e.preventDefault();                
                    window.location=data_url;
                }
            }
        });
    }
    
    /**
     * Return a formatted currency value
     */
    function tm_set_price(value) {
        return accounting.formatMoney(value, {
            symbol: tm_epo_js.currency_format_symbol,
            decimal: tm_epo_js.currency_format_decimal_sep,
            thousand: tm_epo_js.currency_format_thousand_sep,
            precision: tm_epo_js.currency_format_num_decimals,
            format: tm_epo_js.currency_format
        });
    }

    var tm_lazyload_container=false;

    function tm_init_epo(){

        var add_late_fields_prices=function(product_price,bid){
            var total=0;
            $.each(late_fields_prices,function(i,field){
                var price=field["price"],
                    setter=field["setter"],
                    id=setter.attr("name"),
                    hidden=$('#'+id+'_hidden'),
                    bundleid=field["bundleid"];
                
                if (bundleid==bid){
                    if (setter.is("option")){
                        id=setter.closest("select").attr("name");
                        hidden=$('#'+id+'_hidden');
                    }
                    price=(price/100)*product_price;
                    if (setter.data('isset')==1 && field_is_active(setter)){
                        total=total+price;
                    }
                    var formatted_price = tm_set_price(price);
                    setter.data('price', price);
                    setter.data('pricew', price);
                    setter.closest('.tmcp-field-wrap').find('.amount').html(formatted_price);
                    if (hidden.length==0){
                        if (setter.is("option")){
                            setter=setter.closest("select");
                            id=setter.attr("name");
                        }
                        setter.after('<input type="hidden" id="'+id+'_hidden" name="'+id+'_hidden" value="'+price+'" />');
                    }
                    hidden.val(price);
                }else{
                    if (setter.data('pricew')!==undefined){
                        var formatted_price = tm_set_price(setter.data('pricew'));
                        setter.closest('.tmcp-field-wrap').find('.amount').html(formatted_price);
                    }
                }
            });
            late_fields_prices=[];

            return total;
        }

        /**
         * Limit checkbox selection
         */
        $(".tm-extra-product-options input.tm-epo-field[type='checkbox']").change(function () {            
            var allowed=parseInt($(this).attr('data-limit'));
            if (allowed>0){
                var checked = $(this).closest(".tm-extra-product-options-checkbox").find("input.tm-epo-field[type='checkbox']:checked").length;
                if (checked>allowed){
                    $(this).prop("checked", "");
                }
            }
        });

        function tm_set_fee_prices(){
            $(".tmcp-sub-fee-field,.tmcp-fee-field").each(function(i, e) {
                var setter = $(e);
                if ($(e).is('select')) {
                    setter = $(e).find('option:selected');
                }
                var price=setter.data('rules');
                if (price && price[0]){
                    var formatted_price = tm_set_price(price);
                    setter.data('price',price);
                    setter.closest('.tmcp-field-wrap').find('.amount').html(formatted_price);
                }
            });
        }

        function tm_set_subscription_period(){
            $('.tm-epo-totals').each(function(){
                var cart_id=$(this).attr('data-cart-id'),
                    $cart=$('.tm-extra-product-options.tm-cart-'+cart_id),
                    subscription_period=$(this).data('subscription-period'),
                    base=$cart.find('.tmcp-field').closest('.tmcp-field-wrap'),
                    is_subscription=$(this).data('is-subscription');
                if (is_subscription){
                    base.find('.tmperiod').remove();
                    
                    var is_hidden=base.find('.amount').is(".hidden");
                    if (is_hidden){
                        is_hidden=" hidden";
                    }else{
                        is_hidden="";
                    }
                    
                    base.find('.amount').after('<span class="tmperiod'+is_hidden+'"> / '+subscription_period+'</span>');
                    
                    $(this).find('.tmperiod').remove();
                    $(this).find('.amount.options').after('<span class="tmperiod"> / '+subscription_period+'</span>');
                    $(this).find('.amount.final').after('<span class="tmperiod"> / '+subscription_period+'</span>');
                }
            });

        }

        /**
         * Set field price rules
         */
        function tm_epo_rules() {
            late_fields_prices=[];
            var all_carts = $('.cart');              
            if (!all_carts.length>0){
                return;
            }
            all_carts.each(function(cart_index,cart){
                cart=$(cart); 
                var per_product_pricing=true,
                    bto = $(this).closest('.bto_item'),
                    current_variation=cart.find('input[name^=variation_id]').val(),
                    is_bto=false,
                    bundleid=cart.attr( 'data-product_id' );
                if (!bundleid){
                    bundleid=0;
                }

                if (bto.length>0){
                    is_bto=true;
                    var container_id = bto.attr('data-container-id'),
                        price_data = $( '.bto_form_' + container_id ).data( 'price_data' );

                    per_product_pricing = price_data[ 'per_product_pricing' ];
                }
                // get current woocommerce variation
                if (!current_variation) {
                    current_variation = 0;
                }
                if (!is_bto){
                    cart=$('.tm-extra-product-options.tm-cart-main');
                }else{
                    cart=$('.tm-extra-product-options.tm-cart-'+bundleid);
                }
                // set initial prices for all fields
                cart.find('.tmcp-attributes, .tmcp-elements').each(function(index, element) {
                    var rules = $(element).data('rules');
                    // if rule doesn't exit then init an empty rule
                    if (typeof rules !== "object") {
                        rules = {
                            0: "0"
                        };
                    }
                    if (typeof rules === "object") {
                        // we skip price validation test so that every field has at least a price of 0
                        var price = rules[current_variation],
                            formatted_price = tm_set_price(price);

                        $(element).find('.tmcp-field').each(function(i, e) {
                            if (per_product_pricing){
                                $(e).data('price', price);
                                $(e).closest('.tmcp-field-wrap').find('.amount').html(formatted_price);
                            }else{
                                $(e).data('price', 0);
                                $(e).closest('.tmcp-field-wrap').find('.amount').empty();
                            }
                        });
                    }
                });
                // skip specific field rules if per_product_pricing is false
                if (!per_product_pricing){
                    return true;
                }
                
                //  apply specific field rules
                cart.find('.tmcp-field').each(function(index, element) {
                    var setter = $(element);
                    if ($(element).is('select')) {
                        setter = $(element).find('option:selected');
                    }
                    var rules           = setter.data('rules'),
                        rulestype       = setter.data('rulestype'),
                        _rules, 
                        _rulestype, 
                        pricetype, 
                        price, 
                        formatted_price,
                        product_price,
                        cpf_bto_price   = cart.find('.cpf-bto-price');
                    
                    // Composite Products                    
                    if (bto.length>0){                    
                        if (cpf_bto_price.length>0){
                            if (cpf_bto_price.data('per_product_pricing')){
                                product_price = cpf_bto_price.val();
                            }else{
                                product_price = 0;
                            }
                            cpf_bto_price.val(product_price);                        
                        }
                    }else{
                        if (!is_bto){
                            var $totals = $('.tm-epo-totals.tm-cart-main');
                        }else{
                            var $totals = $('.tm-epo-totals.tm-cart-'+bundleid);
                        }
                        if ($totals.length){
                            product_price = $totals.data('price');
                        }
                    }
                                   
                    pricetype='';
                    if (typeof rules === "object") {

                        if (current_variation in rules) {
                            price = rules[current_variation];
                        } else {
                            _rules = $(element).closest('.tmcp-ul-wrap').data('rules');

                            if (typeof _rules === "object") {
                                if (current_variation in _rules) {
                                    price = _rules[current_variation];
                                } else {
                                    price = rules[0];
                                }
                            } else {
                                price = rules[0];
                            }
                        }

                        if (typeof rulestype === "object") {
                            if (current_variation in rulestype) {
                                pricetype = rulestype[current_variation];
                            }else{
                                _rulestype = $(element).closest('.tmcp-ul-wrap').data('rulestype');
                                if (typeof _rulestype === "object") {
                                    if (current_variation in _rulestype) {
                                        pricetype = _rulestype[current_variation];
                                    }else{
                                        pricetype = rulestype[0];
                                    }
                                }else{
                                    pricetype = rulestype[0];
                                }
                            }
                        }else{
                            rulestype = $(element).closest('.tmcp-ul-wrap').data('rulestype');
                            if (typeof rulestype === "object") {
                                if (current_variation in rulestype) {
                                    pricetype = rulestype[current_variation];
                                } else {
                                    pricetype = rulestype[0];
                                }
                            }
                        }
                        if (pricetype=='percent'){
                            price=(price/100)*product_price;
                        }
                        if (pricetype=='percentcurrenttotal'){
                            late_fields_prices.push({"setter":setter,"price":price,"bundleid":bundleid});
                            price=0;
                            setter.data('islate', 1);
                        }
                        if (pricetype=='char'){
                            price=price*setter.val().length;
                        }

                        formatted_price = tm_set_price(price);
                        setter.data('price', price);
                        setter.closest('.tmcp-field-wrap').find('.amount').html(formatted_price);

                    } else {
                        rules = $(element).closest('.tmcp-ul-wrap').data('rules');

                        if (typeof rules === "object") {
                            if (current_variation in rules) {
                                price = rules[current_variation];
                            } else {
                                price = rules[0];
                            }

                            if (typeof rulestype === "object") {
                                if (current_variation in rulestype) {
                                    pricetype = rulestype[current_variation];
                                }else{
                                    _rulestype = $(element).closest('.tmcp-ul-wrap').data('rulestype');
                                    if (typeof _rulestype === "object") {
                                        if (current_variation in _rulestype) {
                                            pricetype = _rulestype[current_variation];
                                        }else{
                                            pricetype = rulestype[0];
                                        }
                                    }else{
                                        pricetype = rulestype[0];
                                    }
                                }
                            }else{
                                rulestype = $(element).closest('.tmcp-ul-wrap').data('rulestype');
                                if (typeof rulestype === "object") {
                                    if (current_variation in rulestype) {
                                        pricetype = rulestype[current_variation];
                                    } else {
                                        pricetype = rulestype[0];
                                    }
                                }
                            }
                            if (pricetype=='percent'){
                                price=(price/100)*product_price;
                            }
                            if (pricetype=='percentcurrenttotal'){
                                late_fields_prices.push({"setter":setter,"price":price,"bundleid":bundleid});
                                price=0;
                                setter.data('islate', 1);
                            }
                            if (pricetype=='char'){
                                price=price*setter.val().length;
                            }

                            formatted_price = tm_set_price(price);
                            setter.data('price', price);
                            setter.closest('.tmcp-field-wrap').find('.amount').html(formatted_price);

                        }

                    }
                });

            });
            
        }

        /**
         * Set event handlers
         */
        function tm_epo_init($form,$formcart) {
            var container_id,item_id="main";
            if (!$form){
                main_cart = $('.cart:last');
                $form = main_cart.parent();                
            }else{
                // Composite bundle id
                container_id = $form.attr('data-container-id');
                item_id = $form.attr('data-item-id');
            }
            var $epo_holder=$('.tm-extra-product-options.tm-cart-'+item_id);
            var $totals_holder = $('.tm-epo-totals.tm-cart-'+item_id);
            
            // update price amount for select elements
            $epo_holder.find('select.tm-epo-field')
            .off('tm-select-change')
            .on('tm-select-change', function() {
                if ($formcart && main_cart && main_cart.data('per_product_pricing')!=undefined && !main_cart.data('per_product_pricing')){
                    return;
                }
                var formatted_price = tm_set_price($(this).find('option:selected').data('price'));
                $(this).closest('.tmcp-field-wrap').find('.amount').html(formatted_price);
                var $cart = $formcart || main_cart;
                $cart.trigger('tm-epo-update');
            });

            // trigger global custom update event for every field
            $epo_holder.find('.tm-epo-field')
            .off('change.cpf tm_trigger_product_image')
            .on('change.cpf tm_trigger_product_image',  function() {
                var $cart = $formcart || main_cart;
                $cart.trigger('tm-epo-update');
                $(this).trigger('tm-select-change');
                if ($(this).is('.tm-product-image:checkbox, .tm-product-image:radio')){
                    var uic=$(this).closest('.tmcp-field-wrap').find('label img');
                    if ($(uic).length>0){
                        if ($(this).is(':checked')){
                            var src=$(uic).first().attr('data-original');
                            if (!src){
                                src=$(uic).first().attr('src');
                            }
                            if (src){
                                $(window).trigger({
                                    "type":"tm_change_product_image",
                                    "src":src,
                                    "element":$(this)
                                });
                            }
                        }else{
                            $(window).trigger({
                                "type":"tm_restore_product_image",
                                "element":$(this)
                            });
                        }
                    }
                }
            });

            $form.find('.cart input.qty')
            .off('change.cpf')
            .on('change.cpf',  function() {              
                var $cart = $formcart || $(this).closest('.cart');
                $cart.trigger('tm-epo-update');
            });          

            // trigger global custom update event when variation changes
            $form.find('.cart')
            .off("change.cpf")
            .on('change.cpf', 'input[name=variation_id]', function(event) {
                var $cart = $formcart || $(this).closest('.cart');                 
                $cart.trigger('tm-epo-update');
                $epo_holder.find("select").trigger('tm-select-change');                
            });

            // global custom update event
            $form.find('.cart')
            .off("tm-epo-update")
            .on('tm-epo-update', function(pass) { 

                tm_epo_rules();
                
                var check_for_bto_internal_show,
                    $cart       = $(this),
                    $_formcart  = $formcart || $cart;
                if ($formcart){
                    $totals_holder.addClass("cpf-bto-totals");
                }
                var product_price       = 0,
                    total               = 0,
                    product_type        = $totals_holder.data('type'),
                    show_total          = false,
                    qty_element         = $cart.find('input.qty'),
                    qty                 = parseFloat(qty_element.val()),
                    cpf_bto_price       = $_formcart.find('.cpf-bto-price'),
                    per_product_pricing = true,
                    is_bto=false,bundleid=$_formcart.attr( 'data-product_id' );

                if (isNaN(qty)){
                    if ($totals_holder.attr("data-is-sold-individually") || qty_element.length==0){
                        qty=1;
                    }
                }
                
                if (!bundleid){
                    bundleid=0;
                }

                if ($totals_holder.length){
                    product_price = $totals_holder.data('price');
                }else{
                    if (cpf_bto_price.length>0){
                        product_price = cpf_bto_price.val();
                    }
                }

                // Composite Products
                if ($formcart && $cart.find('.cpf-bto-price').length>0){
                    is_bto=true;
                    product_price=parseFloat($cart.find('.cpf-bto-price').val());
                    per_product_pricing=$cart.find('.cpf-bto-price').data('per_product_pricing');

                }else if (!$formcart && $('.cpf-bto-price').length>0){
                    
                    check_for_bto_internal_show=1;                   
                    
                    $('.cpf-bto-price').each(function(){                        
                        if (!isNaN( parseFloat($(this).val()))){
                            var _qty=$(this).closest('.cart').find('input.qty');
                            if (_qty.length>0){
                                _qty=parseFloat(_qty.val());
                            }else{
                                _qty=1;
                            }
                            product_price=parseFloat(product_price)+parseFloat($(this).val()*_qty);
                        }
                    });
                    
                    $('.cpf-bto-optionsprice').each(function(){
                        if (!isNaN( parseFloat($(this).val()))){
                            product_price=parseFloat(product_price)+parseFloat($(this).val());
                        }
                    });
                    
                }                
                if ($formcart || (main_epo_inside_form && tm_epo_js.tm_epo_totals_box_placement=="woocommerce_before_add_to_cart_button")){
                    if (product_type == 'variable'  && !$totals_holder.data("moved_inside")) {
                        $cart.find('.single_variation').after($totals_holder);
                        $totals_holder.data("moved_inside",1);
                    }
                }
                /* move total box of main cart if is composite */
                if (main_epo_inside_form && tm_epo_js.tm_epo_totals_box_placement=="woocommerce_before_add_to_cart_button"){
                    if (product_type == 'bto' && !$totals_holder.data("moved_inside")) {
                        $cart.find('.bundle_price').after($totals_holder);
                        $totals_holder.data("moved_inside",1);
                    }
                }

                $epo_holder.find('.tmcp-field').each(function() {
                    if ($(this).is(':checkbox, :radio, :input')) {
                        if ( field_is_active( $(this) ) ){ 
                            var option_price = 0;
                            if ($(this).is('.tmcp-checkbox, .tmcp-radio')) {
                                if ($(this).is(':checked')) {
                                    option_price = $(this).data('price');
                                    show_total = true;
                                    $(this).data('isset',1);
                                }else{
                                    $(this).data('isset',0);
                                }
                            } else if ($(this).is('.tmcp-select')) {
                                option_price = $(this).find('option:selected').data('price');
                                show_total = true;
                                $(this).find('option').data('isset',0);
                                $(this).find('option:selected').data('isset',1);
                            } else {
                                if ($(this).val()) {
                                    option_price = $(this).data('price');
                                    show_total = true;
                                    $(this).data('isset',1);
                                }else{
                                    $(this).data('isset',0);
                                }
                            }
                            if (!option_price) {
                                option_price = 0;
                            }
                            total = parseFloat(total) + parseFloat(option_price);
                        }
                    }
                });
                
                var subscription_options_total=0;
                var cart_fee_options_total=0;
                $epo_holder.find('.tmcp-sub-fee-field,.tmcp-fee-field').each(function() {
                    if ($(this).is(':checkbox, :radio, :input')) {
                        if ( field_is_active( $(this) ) ){ 
                            var option_price = 0;
                            if ($(this).is('.tmcp-checkbox, .tmcp-radio')) {
                                if ($(this).is(':checked')) {
                                    option_price = $(this).data('price');
                                    show_total = true;
                                    $(this).data('isset',1);
                                }else{
                                    $(this).data('isset',0);
                                }
                            } else if ($(this).is('.tmcp-select')) {
                                option_price = $(this).find('option:selected').data('price');
                                show_total = true;
                                $(this).find('option').data('isset',0);
                                $(this).find('option:selected').data('isset',1);
                            } else {
                                if ($(this).val()) {
                                    option_price = $(this).data('price');
                                    show_total = true;
                                    $(this).data('isset',1);
                                }else{
                                    $(this).data('isset',0);
                                }
                            }
                            if (!option_price) {
                                option_price = 0;
                            }
                            if ($(this).is('.tmcp-sub-fee-field')){
                                subscription_options_total = parseFloat(subscription_options_total) + parseFloat(option_price);
                            }
                            if ($(this).is('.tmcp-fee-field')){
                                cart_fee_options_total = parseFloat(cart_fee_options_total) + parseFloat(option_price);
                            }
                        }
                    }
                });
                if(cart_fee_options_total>0){                    
                    show_total=true;
                }

                if ($totals_holder.attr('data-type')=="bto"){
                    var bto_show=$('.tm-epo-totals.tm-cart-main').data('btois');
                    if (bto_show==='show'){
                        show_total=true;
                    }
                }
                
                if (check_for_bto_internal_show){
                    show_total=true;
                }
                
                if ($formcart && !per_product_pricing){
                    show_total=false;
                }

                if(tm_epo_js.tm_epo_final_total_box=='pxq'){
                    show_total=true;
                }

                if (show_total && qty > 0) {
                    /* hide native prices */
                    $cart.find('.single_variation .price,.bundle_price .price,.bto_item_wrap .price').hide();

                    var _total=total;

                    total = parseFloat(total * qty);

                    var formatted_options_total = tm_set_price(total),
                        formatted_final_total,
                        extra_fee=0;
                    
                    if (tm_epo_js.extra_fee){
                        extra_fee=parseFloat(tm_epo_js.extra_fee);
                        if (isNaN(extra_fee)){
                            extra_fee=0;
                        }
                    }

                    var product_total_price = parseFloat(product_price * qty);                        
                    var late_total_price= add_late_fields_prices(parseFloat(product_price) + parseFloat(_total),bundleid);                                                
                    _total = _total + late_total_price;
                    total = parseFloat(_total * qty);
                    var total_plus_fee=parseFloat(total)+parseFloat(cart_fee_options_total);
                    formatted_options_total = tm_set_price(total_plus_fee);
                    product_total_price = parseFloat(product_total_price + total_plus_fee + extra_fee);
                    formatted_final_total = tm_set_price(product_total_price);

                    var html = '<dl class="tm-extra-product-options-totals tm-custom-price-totals">';
                    if (tm_epo_js.tm_epo_final_total_box!='pxq' && tm_epo_js.tm_epo_final_total_box!='final' && tm_epo_js.tm_epo_final_total_box!='hide' && (!(total==0 && tm_epo_js.tm_epo_final_total_box=='hideoptionsifzero')) ){                        
                        html = html + '<dt class="tm-options-totals">' + tm_epo_js.i18n_options_total + '</dt><dd class="tm-options-totals"><span class="amount options">' + formatted_options_total + '</span></dd>';
                    }
                    if (extra_fee) {
                        var formatted_extra_fee=tm_set_price(extra_fee);
                        html = html + '<dt class="tm-extra-fee">' + tm_epo_js.i18n_extra_fee + '</dt><dd class="tm-extra-fee"><span class="amount options extra-fee">' + formatted_extra_fee + '</span></dd>';
                    }
                    if (formatted_final_total && tm_epo_js.tm_epo_final_total_box!='hide') {                        
                        html = html + '<dt class="tm-final-totals">' + tm_epo_js.i18n_final_total + '</dt><dd class="tm-final-totals"><span class="amount final">' + formatted_final_total + '</span></dd>';
                    }
                    if ($totals_holder.data('is-subscription') ) {
                        var subscription_total=parseFloat($totals_holder.data('subscription-sign-up-fee'))+parseFloat(subscription_options_total);
                        var formatted_subscription_fee_total=tm_set_price(subscription_total);
                        html = html + '<dt class="tm-subscription-fee">' + tm_epo_js.i18n_sign_up_fee + '</dt><dd class="tm-subscription-fee"><span class="amount subscription-fee">' + formatted_subscription_fee_total + '</span></dd>';
                    }
                    html = html + '</dl>';
                    if (tm_epo_js.tm_epo_final_total_box=='hide'){
                        html='';
                        if (formatted_final_total){
                            $_formcart.find(".single_variation .price .amount, .bundle_price .price .amount").html(formatted_final_total);
                        }
                    }
                    $totals_holder.html(html).show();

                    if ($formcart){
                        if (per_product_pricing){
                            $cart.find('.cpf-bto-optionsprice').val(parseFloat(total));
                        }
                        main_cart.trigger("tm-epo-update");
                    }else{
                        $('.tm-epo-totals.tm-cart-main').data('is_active',true);
                        tm_set_subscription_period();
                    }
                } else {
                    /* show native prices */
                    $cart.find('.single_variation .price,.bundle_price .price,.bto_item_wrap .price').show();
                    
                    $totals_holder.empty().hide();;

                    if ($formcart){
                        if (per_product_pricing){
                            $cart.find('.cpf-bto-optionsprice').val(parseFloat(total*qty));    
                        }                        
                        main_cart.trigger("tm-epo-update");
                    }
                }
                if (container_id){
                    $( '.bto_form_' + container_id ).trigger('cpf_bto_review');
                }
                main_cart.trigger("tm-epo-after-update");
            });

            // update prices when a variation is found
            $form.find('.variations_form').on('found_variation', function(event, variation) {
                var $variation_form = $(this),                    
                    variations      = $totals_holder.data('variations'),
                    variations_subscription_sign_up_fee = $totals_holder.data('variations-subscription-sign-up-fee'),
                    variations_subscription_period = $totals_holder.data('variations-subscription-period'),
                    product_price;

                if (variations && variation.variation_id && variations_subscription_sign_up_fee[variation.variation_id]){
                    $totals_holder.data('subscription-sign-up-fee', variations_subscription_sign_up_fee[variation.variation_id]);
                }
                if (variations && variation.variation_id && variations_subscription_period[variation.variation_id]){
                    $totals_holder.data('subscription-period', variations_subscription_period[variation.variation_id]);
                }

                if (variations && variation.variation_id && variations[variation.variation_id]){
                    product_price=variations[variation.variation_id];
                    $totals_holder.data('price', product_price);
                }
                else if ($(variation.price_html).find('.amount:last').size()) {
                    product_price = $(variation.price_html).find('.amount:last').text();
                    product_price = product_price.replace(tm_epo_js.currency_format_thousand_sep, '');
                    product_price = product_price.replace(tm_epo_js.currency_format_decimal_sep, '.');
                    product_price = product_price.replace(/[^0-9\.]/g, '');
                    product_price = parseFloat(product_price);
                    $totals_holder.data('price', product_price);
                }
                $('.tm-totals-form-'+item_id).find('.cpf-product-price').val(product_price);                
                $variation_form.trigger('tm-epo-update');                
            });
            $form.find('.variations select').on('blur',function() {
                var $variation_form = $(this).closest('.cart');
                $variation_form.trigger('tm-epo-update');
            });

        }

        function bto_support(){

            var $totals = $('.tm-epo-totals.tm-cart-main');

            $('.bto_item')
            .on('found_variation.cpf', function(event, variation) {
                var $bto            = $(this),
                    item            = $(this),
                    container_id    = item.attr('data-container-id'),
                    price_data      = $( '.bto_form_' + container_id ).data( 'price_data' ),
                    product_price,
                    item_id         = item.attr('data-item-id');

                $(".bto_form").find( ' .review .price_' + item_id ).removeData('cpf_review_price');
                $(".bto_form").find( ' .review .price_' + item_id ).find('.amount').empty();

                if ( price_data[ 'per_product_pricing' ] == true ) {                   
                    product_price = parseFloat(variation.price);
                }
                $bto.find('.cpf-bto-price').data('per_product_pricing',price_data[ 'per_product_pricing' ] );
                $bto.find('.cpf-bto-price').val(product_price);
                main_cart.data('per_product_pricing',price_data[ 'per_product_pricing' ] );

                $bto.find('.cart').trigger('tm-epo-update');
                $totals.data('btois','none');

            })
            .on( 'wc-composite-item-updated.cpf', function() {
                tm_lazyload();
                $(".tm-collapse").tmtoggle();

                var $bto = $(this);

                tm_css_styles($bto);
                /**
                 * Start Condition Logic
                 */
                cpf_section_logic($bto.find(".tm-extra-product-options"));
                cpf_element_logic($bto.find(".tm-extra-product-options"));

                var item            = $(this),
                    container_id    = item.attr('data-container-id'),
                    price_data      = $( '.bto_form_' + container_id ).data( 'price_data' ),
                    product_price,
                    item_id         = item.attr('data-item-id');

                $(".bto_form").find( ' .review .price_' + item_id ).removeData('cpf_review_price');
                $(".bto_form").find( ' .review .price_' + item_id ).find('.amount').empty();

                if ( price_data[ 'per_product_pricing' ] == true ) {                   
                    product_price = parseFloat($bto.find( '.bto_item_data' ).data( 'price' ));
                }
                $bto.find('.cpf-bto-price').data('per_product_pricing',price_data[ 'per_product_pricing' ] );
                $bto.find('.cpf-bto-price').val(product_price);
                main_cart.data('per_product_pricing',price_data[ 'per_product_pricing' ] );

                tm_epo_init($(this),$(this).find('.cart'));
                main_cart.trigger('tm-epo-update');

            })           
            .on( 'change', '.bto_item_options select', function( event ) {
                var item    = $(this),
                    item_id = item.attr('data-item-id');

                $(".bto_form").find( ' .review .price_' + item_id ).removeData('cpf_review_price');
                $(".bto_form").find( ' .review .price_' + item_id ).find('.amount').empty();
                if ($(this).val()=== ''){                                                         
                    $totals.data('passed',false);
                    $totals.data('btois','none');
                }else{
                    main_cart.trigger('tm-epo-update');
                }
            } )
            .on( 'woocommerce_variation_select_change.cpf', function( event ) {
                var item    = $(this),
                    item_id = item.attr('data-item-id');

                $(".bto_form").find( ' .review .price_' + item_id ).removeData('cpf_review_price');
                $(".bto_form").find( ' .review .price_' + item_id ).find('.amount').empty();
                if ($(this).find( '.variations .attribute-options select' ).val()===''){                                                     
                    $totals.data('passed',false);
                    $totals.data('btois','none');
                }
            });

            $('.bundle_wrap').on('show_bundle.cpf',function(){
                var $bto=$(this).closest('.cart'),
                    id=$bto.attr('data-container-id');
                check_bto(id);                
            });

            $( '.bto_form'  )
            .off( 'woocommerce-product-addons-update.cpf cpf_bto_review')
            .on( 'woocommerce-product-addons-update.cpf cpf_bto_review', function() {
                var bto_form=$(this);
                $(this).parent().find( '.bto_item' ).each( function(){
                    var item        = $(this),
                        item_id     = item.attr('data-item-id'),
                        html        = bto_form.find( ' .review .price_' + item_id ),
                        value,
                        options     = item.find(".cpf-bto-optionsprice").val();

                    if (html.data('cpf_review_price')){
                        value = accounting.unformat(html.data('cpf_review_price'));
                    }else{
                        value = accounting.unformat(html.find('.amount').html());
                        html.data('cpf_review_price',value);
                    }

                    if (options){
                        var total = parseFloat(value)+parseFloat(options);
                        html.find('.amount').html(tm_set_price(total));
                    }
                });                        

            } );

            $('.bto_item').trigger('wc-composite-item-updated.cpf');

        }

        function check_bto(id){
            var show=true;
            var $totals = $('.tm-epo-totals.tm-cart-main');
            $( '.bto_form_' + id ).parent().find( '.bto_item' ).each( function(){
                var item        = $(this),
                    item_id         = item.attr('data-item-id'),
                    form_data       = $( '.bto_form_' + id + ' .bundle_wrap .bundle_button .form_data_' + item_id ),
                    product_input   = form_data.find( 'input.product_input' ).val(),
                    quantity_input  = form_data.find( 'input.quantity_input' ).val(),
                    variation_input = form_data.find( 'input.variation_input' ).val(),
                    product_type    = item.find( '.bto_item_data' ).data( 'product_type' );
                
                if ( product_type == undefined || product_type == '' || product_input === '' ){
                    show = false;
                }
                else if ( product_type != 'none' && quantity_input == '' ){
                    show = false;
                }
                else if ( product_type == 'variable' && variation_input == undefined ) {
                    show = false;
                }
            });
            
            if (show){
                $totals.data('btois','show');
            }else{
                $totals.data('btois','none');
            }
            main_cart.trigger('tm-epo-update');
        }

        function tm_lazyload(){
            if (tm_epo_js.tm_epo_no_lazy_load=="yes"){
                return;
            }
            if (tm_lazyload_container){
                $("img.tmlazy").lazyload({
                    event : "scroll tmlazy",
                    container:$('.quick-view-content'),
                    skip_invisible : false
                });
            }else{
                $("img.tmlazy").lazyload({
                    event : "scroll tmlazy",
                    skip_invisible : false
                });
            }
        }

        function tm_css_styles(obj){            
            if (tm_epo_js.css_styles=='on'){
                if (!obj){
                    $('.tm-extra-product-options .tm-epo-field.tmcp-checkbox').not('.use_images,.tm-styled').addClass('tm-styled').prettyCheckable({color:tm_epo_js.css_styles_style});
                    $('.tm-extra-product-options .tm-epo-field.tmcp-radio').not('.use_images,.tm-styled').addClass('tm-styled').prettyCheckable({color:tm_epo_js.css_styles_style});
                }else{
                    $(obj).find('.tm-extra-product-options .tm-epo-field.tmcp-checkbox').not('.use_images,.tm-styled').addClass('tm-styled').prettyCheckable({color:tm_epo_js.css_styles_style});
                    $(obj).find('.tm-extra-product-options .tm-epo-field.tmcp-radio').not('.use_images,.tm-styled').addClass('tm-styled').prettyCheckable({color:tm_epo_js.css_styles_style});
                }
            }
        }

        function tm_product_image(){
            var img=$(".product .images img").not('.thumbnails img')
            if ($(img).length>1){
                img=$(img).first();
            }
            if ($(img).length>0){
                $(window).on('tm_change_product_image',function(e){
                    var tm_current_image_element_id=e.element.attr('name');
                    $('#'+tm_current_image_element_id+'_tmimage').remove();
                    $('.tm-clone-product-image').hide();
                    var clone_image=$(img).tm_clone();
                    clone_image
                    .prop('src',e.src)
                    .attr('id',tm_current_image_element_id+'_tmimage')
                    .addClass('tm-clone-product-image').show();

                    $(img).hide().after(clone_image);

                });
                $(window).on('tm_restore_product_image',function(e){
                    var tm_current_image_element_id=e.element.attr('name');
                    $('#'+tm_current_image_element_id+'_tmimage').remove();
                    if($('.tm-clone-product-image').length==0){
                        $(img).show();
                    }else{
                        var len=$('.tm-clone-product-image').length;
                        var current_element,found=false;
                        for (var i = len - 1; i >= 0; i--) {
                            current_element=$('.tm-clone-product-image').eq(i).attr('id').replace('_tmimage','');
                            
                            if ($('[name="'+current_element+'"]').closest(".cpf_hide_element").is(":visible")){
                                $('.tm-clone-product-image').eq(i).show();
                                found=true;
                                break;
                            }else{
                                $('.tm-clone-product-image').eq(i).hide();
                            }
                        };
                        if(!found){
                            $(img).show();
                        }
                        
                    }
                });
                $('.tm-product-image:checked').last().trigger('tm_trigger_product_image');
            }
        }

        tm_set_datepicker();
        tm_set_range_pickers();

        tm_set_url_fields();
        tm_set_fee_prices();
        tm_set_subscription_period();
        $.tm_tooltip();

        $(".tm-collapse").tmtoggle();
        $(".tm-cart-link").tmpoplink();
        $(".tm-section-link").tmsectionpoplink();

        $( 'body' ).on('updated_checkout' ,function(){$(".tm-cart-link").tmpoplink();});

        /**
         * Start Condition Logic
         */
        cpf_section_logic($(".tm-extra-product-options"));
        cpf_element_logic($(".tm-extra-product-options"));
        
        /**
         * Holds the active precentage of total current price type fields
         */
        var late_fields_prices=[];


        // Init field price rules
        tm_epo_rules();
        late_fields_prices=[];
        if ($('.variations_form').length > 0) {
            $('.variations_form').on('wc_variation_form.cpf', function() {
                tm_epo_init();
                $('.cart').trigger('tm-epo-update');
            });
        } else {
            tm_epo_init();
            bto_support();
            $('.cart').trigger('tm-epo-update');
        }

        tm_lazyload();
        tm_product_image();
        tm_css_styles();

    }

    function tm_check_main_cart(){
        if (!main_cart){
             main_cart = $('.cart:last');
        }
        var form;
        if (main_cart.is("form.cart")){
            form=main_cart;
        }else{
            form=main_cart.closest("form");
        }
        var main_epo_inside_form_check=form.find(".tm-extra-product-options.tm-cart-main").length;
        
        if (main_epo_inside_form_check>0){
            main_epo_inside_form=true;
        }

        if (!main_epo_inside_form){
            form.on("submit",function(){
                var epos=$('.tm-extra-product-options.tm-cart-main').tm_clone();
                var epos_hidden=$('.tm-totals-form-main').tm_clone();
                var formepo=$('<div class="tm-hidden"></div>');
                formepo.append(epos);
                formepo.append(epos_hidden);
                form.append(formepo);
                return true;
            });
        }
    }

    /**
     * Holds the main cart when using Composite Products
     */
    var main_cart=false;
    
    var main_epo_inside_form=false;

    $(document).ready(function() {
        
        tm_check_main_cart();

        tm_init_epo();
        
        $('body').on('quick-view-displayed',function(){
            tm_lazyload_container=$('.quick-view-content');
            tm_init_epo();
        });

        $(window).trigger("tmlazy");
        
    });
})(jQuery);
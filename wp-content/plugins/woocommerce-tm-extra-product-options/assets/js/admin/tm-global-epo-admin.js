(function($) {
    "use strict";

    $.tmEPOAdmin = {

        initialitize: function() {
            
            $.tmEPOAdmin.isinit=true;
            // Sections sortable
            $(".builder_layout").sortable({
                handle: ".move",
                cursor: "move",
                items: ".builder_wrapper",
                stop: function(e, ui) {
                    $.tmEPOAdmin.builder_reorder_multiple();
                },
                forcePlaceholderSize: true,
                placeholder: 'bitem pl2',
                tolerance: 'pointer'
            });
            

            // Elements sortable
            $.tmEPOAdmin.builder_items_sortable($(".builder_wrapper .bitem_wrapper"));

            // Elements draggable
            $(".builder_elements .ditem").draggable({
                zIndex: 5000,
                scroll: true,
                helper: "clone",
                start: function(event, ui) {
                    var current = $(event.target);
                    current.css({
                        opacity: 0.3
                    });
                    $(".builder_layout .bitem_wrapper").addClass("highlight");
                },
                stop: function(event, ui) {
                    $(".builder_layout .bitem_wrapper").removeClass("highlight");
                    $(event.target).css({
                        opacity: 1
                    });
                },
                connectToSortable: ".builder_layout .bitem_wrapper"
            });

            // Export button
            $(document).on("click.cpf", "#builder_export", function(e) {
                e.preventDefault();
                var $this=$(this);
                if ($this.data('doing_export')){
                    return;
                }
                $this.data('doing_export',1);
                var tm_meta,data,frame;               
                
                tm_meta = $.tmEPOAdmin.prepare_for_json($("#post").serializeObject());
                tm_meta = $.toJSON(tm_meta);
                
                data = {
                    action: 'tm_export',
                    metaserialized:tm_meta,
                    security: tm_epo_admin.export_nonce
                };

                $.post(tm_epo_admin.ajax_url, data, function(response) {
                    if (response && response.result && response.result !=''){
                        window.location = response.result;                        
                    }                    
                },'json')
                .always(function(response) {
                     $this.data('doing_export',0);
                });
            });

            // Import button
            $('#builder_import_file').fileupload({
                dataType: 'json',
                url: tm_epo_admin.import_url,
                formData:{'action':'import'},
                add:function (e, data) {
                    var $_html = $.tmEPOAdmin.builder_floatbox_template_import({
                        "id": "temp_for_floatbox_insert",
                        "html": "",
                        "title": tm_epo_admin.import_title
                    });

                    data._to = $("body").addClass("overflow").tm_floatbox({
                        "fps": 1,
                        "ismodal": true,
                        "refresh": 100,
                        "width": "50%",
                        "height": "300px",
                        "classname": "flasho tm_wrapper",
                        "data": $_html
                    });
                    var $progress=$('<div class="tm_progress_bar tm_orange"><span class="tm_percent"></span></div><div class="tm_progress_info"><span class="tm_info"></span></div>');
                    $progress.appendTo("#temp_for_floatbox_insert");

                    $(".details_cancel").click(function() {
                        data.abort();
                        $("body").removeClass("overflow");
                        if (data._to) data._to.cancelfunc(); 
                    });

                    $('.tm_info').html(tm_epo_admin.i18n_importing);

                    if (data.autoUpload || (data.autoUpload !== false &&
                            $(this).fileupload('option', 'autoUpload'))) {
                        data.process().done(function () {
                            data.submit();
                        });
                    }
                },
                done: function (e, data) {
                    if (data.result && data.result.message){                        
                        $('.tm_info').html(data.result.message);
                    }
                    if (data.result && data.result.result==1){                        
                        $('.tm_progress_bar').removeClass('tm_orange').addClass('tm_turquoise');
                        $('.tm_info').addClass('tm_color_turquoise');
                        $(".details_cancel").remove();
                        $('.tm_info').html(tm_epo_admin.i18n_saving);
                        $(window).off( 'beforeunload.edit-post' );
                        $("#post").submit();
                    }else{
                        $('.tm_progress_bar').removeClass('tm_orange').addClass('tm_pomegranate');
                        $('.tm_info').addClass('tm_color_pomegranate');
                    }
                },
                fail: function (e, data) {
                    if (data.result && data.result.message){
                        $('.tm_info').addClass('tm_color_pomegranate').html(data.result.message);
                    }
                    $('.tm_progress_bar').removeClass('tm_orange').addClass('tm_pomegranate');
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('.tm_progress_bar').css('width',progress + '%');
                    $('.tm_percent').html(progress + '%');
                },
                always: function (e, data) {
                    $("body").removeClass("overflow");
                }
            });
            $(document).on("click.cpf", "#builder_import", function(e) {
                e.preventDefault();                
                $('#builder_import_file').click();                
            });

            // Fullsize button
            $(document).on("click.cpf", "#builder_fullsize", function(e) {
                e.preventDefault();
                $("body").addClass("overflow");
                var _fullsize=$(".tm_mode_builder");
                if (!_fullsize.length){
                    _fullsize=$('.builder_selector').closest('.postbox');
                }
                _fullsize.css({
                    opacity: 0
                }).addClass("fullsize");
                $('<div class="fl-overlay forfullsize"></div>').css({
                    zIndex: 10000000,
                    opacity: 1,
                    height: 0
                }).appendTo("body").animate({
                    opacity: 1,
                    height: "100%"
                }, 500, "easeOutExpo", function() {
                    _fullsize.css({
                        opacity: 1
                    });
                    $(".forfullsize").animate({
                        opacity: 0
                    }, 300, "easeInExpo", function() {
                        $(".forfullsize").remove();
                    });
                });
            });

            // Close Fullsize button
            $(document).on("click.cpf", "#builder_fullsize_close", function(e) {
                e.preventDefault();
                var _fullsize=$(".tm_mode_builder");
                if (!_fullsize.length){
                    _fullsize=$('.builder_selector').closest('.postbox');
                }
                $('<div class="fl-overlay forfullsize"></div>').css({
                    zIndex: 10000000,
                    opacity: 1,
                    height: 0
                }).appendTo("body").animate({
                    opacity: 1,
                    height: "100%"
                }, 500, "easeOutExpo", function() {
                    _fullsize.removeClass("fullsize");
                    $("body").removeClass("overflow");
                    $(".forfullsize").animate({
                        opacity: 0
                    }, 300, "easeInExpo", function() {
                        $(".forfullsize").remove();
                    });
                });
            });

            // Add Element button
            $(document).on("click.cpf", ".builder_add", $.tmEPOAdmin.builder_add_onClick);
            $(document).on("click.cpf", ".builder_add_on_section", $.tmEPOAdmin.builder_add_on_section_onClick);
            $(document).on("click.cpf", ".builder_drag_elements.float .ditem", $.tmEPOAdmin.builder_float_add_onClick);
            // Section add button
            $(document).on("click.cpf", ".builder_add_section", $.tmEPOAdmin.builder_add_section_onClick);

            // Section edit button
            $(document).on("click.cpf", ".builder_wrapper .btitle .edit", $.tmEPOAdmin.builder_section_item_onClick);
            // Section clone button
            $(document).on("click.cpf", ".builder_wrapper .btitle .clone", $.tmEPOAdmin.builder_section_clone_onClick);
            // Section plus button
            $(document).on("click.cpf", ".builder_wrapper .btitle .plus", $.tmEPOAdmin.builder_section_plus_onClick);
            // Section minus button
            $(document).on("click.cpf", ".builder_wrapper .btitle .minus", $.tmEPOAdmin.builder_section_minus_onClick);
            // Section delete button
            $(document).on("click.cpf", ".builder_wrapper .btitle .delete", $.tmEPOAdmin.builder_section_delete_onClick);
            // Section fold button
            $(document).on("click.cpf", ".builder_wrapper .btitle .fold", $.tmEPOAdmin.builder_section_fold_onClick);

            // Element edit button
            $(document).on("click.cpf", ".bitem .edit", $.tmEPOAdmin.builder_item_onClick);
            // Element clone button
            $(document).on("click.cpf", ".bitem .clone", $.tmEPOAdmin.builder_clone_onClick);
            // Element plus button
            $(document).on("click.cpf", ".bitem .plus", $.tmEPOAdmin.builder_plus_onClick);
            // Element minus button
            $(document).on("click.cpf", ".bitem .minus", $.tmEPOAdmin.builder_minus_onClick);
            // Element delete button
            $(document).on("click", ".bitem .delete", $.tmEPOAdmin.builder_delete_onClick);

            // Add options button
            $(document).on("click.cpf", ".builder-panel-add", $.tmEPOAdmin.builder_panel_add_onClick);
            // Mass add options button
            $(document).on("click.cpf", ".builder-panel-mass-add", $.tmEPOAdmin.builder_panel_mass_add_onClick);
            // Populate options button
            $(document).on("click.cpf", ".builder-panel-populate", $.tmEPOAdmin.builder_panel_populate_onClick);
            // Delete options button
            $(document).on("click.cpf", ".builder_panel_delete", $.tmEPOAdmin.builder_panel_delete_onClick);
            $(".builder_panel_delete").on("click.cpf", $.tmEPOAdmin.builder_panel_delete_onClick); //sortable bug
            // Auto generate option value
            $(document).on("keyup.cpf change.cpf", ".tm_option_title", function() {
                $(this).closest('.options_wrap').find('.tm_option_value').val($(this).val());
            });
            // Upload button
            $(document).on("click.cpf", ".tm_upload_button", $.tmEPOAdmin.upload);
            $(document).on("change.cpf", ".use_images", $.tmEPOAdmin.tm_upload);
            $(document).on("change.cpf", ".use_url", $.tmEPOAdmin.tm_url);
            
            // popup editor identification
            $(document).on("click.cpf", ".tm_editor_wrap", function() {
                var t = $(this).find('textarea');
                if (t.attr('id')) {
                    window.wpActiveEditor = t.attr('id');
                }
            });

            $(document).on("change.cpf", ".cpf-logic-element", function(e,ison) {
                if (ison===undefined){
                    ison=$(this).closest(".section_elements, .builder_wrapper, .bitem, .builder_element_wrap");
                    if (ison.is(".section_elements") || ison.is(".builder_wrapper")){
                        ison=false;
                    }else{
                        ison=true;
                    }
                }
                var logic;
                if (!ison){
                    logic=$.tmEPOAdmin.logic_object;
                }else{
                    logic=$.tmEPOAdmin.element_logic_object;
                }
                var element=$(this).val();
                var section=$(this).children('option:selected').attr('data-section');
                var cpf_logic_value=logic;
                if (section in cpf_logic_value){
                    cpf_logic_value=logic[section].values;
                    if (element in cpf_logic_value){
                        cpf_logic_value=logic[section].values[element];
                    }else{
                        cpf_logic_value=false;
                    }                
                }else{
                    cpf_logic_value=false;
                }                
                if (cpf_logic_value){
                    cpf_logic_value=$(cpf_logic_value);
                    var select=$(this).closest('.tm-logic-rule').find('.tm-logic-value');
                    select.empty().append(cpf_logic_value);
                }
            });

            $(document).on("change.cpf", ".cpf-logic-operator", function(e,ison) {
                var value=$(this).val();
                var select=$(this).closest('.tm-logic-rule').find('.tm-logic-value');
                if (value=='isempty' || value=='isnotempty'){
                    select.hide();
                }else{
                    select.show();
                }
            });

            $(document).on("change.cpf", ".activate-sections-logic, .activate-element-logic", function() {
                var value=parseInt($(this).val());
                if (value==1){
                    $(this).parent().find(".builder-logic-div").show();
                }else{
                    $(this).parent().find(".builder-logic-div").hide();
                }
            });
            $(document).on("dblclick.cpf", ".tm-default-radio", function() {
                $(this).removeAttr("checked").prop("checked",false);
            });     

            $(document).on("click.cpf", ".cpf-add-rule", $.tmEPOAdmin.cpf_add_rule);
            $(document).on("click.cpf", ".cpf-delete-rule", $.tmEPOAdmin.cpf_delete_rule);

            // Check section logic
            $.tmEPOAdmin.check_section_logic();
            // Check element logic
            $.tmEPOAdmin.check_element_logic();
            // Start logic
            $.tmEPOAdmin.section_logic_start();
            $.tmEPOAdmin.element_logic_start();

            // Prevent refresh page changes to hidden elements
            $.tmEPOAdmin.set_hidden();

            $.tmEPOAdmin.set_field_title();

            $(document).on("changetitle.cpf", ".tm-header-title", function() {
                $.tmEPOAdmin.set_field_title($(this));
            });

            $(".tm-tabs").tmtabs();

            $("select.ajax_chosen_select_tm_product_ids").ajaxChosen({
                method:     'GET',
                url:        tm_epo_admin.ajax_url,
                dataType:   'json',
                afterTypeDelay: 100,
                data:       {
                    action:         'woocommerce_json_search_products',
                    security:       tm_epo_admin.search_products_nonce
                }
            }, function (data) {

                var terms = {};

                $.each(data, function (i, val) {
                    terms[i] = val;
                });

                return terms;
            });

            $.tmEPOAdmin.disable_categories();
            $(document).on("click.cpf", ".meta-disable-categories", function() {
                $.tmEPOAdmin.disable_categories();
            });
            $.tmEPOAdmin.fix_content_float();
            $.tmEPOAdmin.init_sections_check();
            $("body").on("woocommerce-product-type-change", function() {
                $.tmEPOAdmin.init_sections_check();
                $.tmEPOAdmin.fix_content_float(); 
            });
            
            $.tmEPOAdmin.fix_form_submit();

            $.tmEPOAdmin.isinit=false;            
        },       

        prepare_for_json:function(data){
            var result= {},arr,obj,value,must_be_array;
            for (var i in data){
                if (i.indexOf("tm_meta[") == 0){
                    arr = i.split(/[[\]]{1,2}/);
                    arr.pop();
                    arr = arr.map(function(item){ return item === '' ? null : item });
                    if (arr.length>0 && arr[arr.length-1]==null){
                        must_be_array=true;
                    }else{
                        must_be_array=false;
                    }
                    arr=arr.filter(function(v, k, el) {
                        if (v !== null && v !== undefined) {
                            return v;
                        }
                    });
                    if (typeof data[i] !="object" && must_be_array){
                        value=[data[i]];
                    }else{
                        value=data[i];    
                    }                    
                    result=$.tmEPOAdmin.constructObject(arr,value,result);
                }           
            }
            return result;
        },

        constructObject:function(a, final_value,obj) {
            var val=a.shift();
            if (a.length>0) {
                if(!obj.hasOwnProperty(val)){
                    obj[val]={};
                }
                obj[val]=$.tmEPOAdmin.constructObject(a,final_value,obj[val]);
            } else {
                obj[val]=final_value;
            }
            return obj;
        },

        did_post_preview:0,

        fix_form_submit: function(){          
            $("#post").submit(function(){
                var tm_meta,data,tm_meta_serialized;
                tm_meta=$(this).find('[name^="tm_meta["]');
                tm_meta.attr("disabled", false);
                data = $.tmEPOAdmin.prepare_for_json($(this).serializeObject());
                data=$.toJSON(data);
                tm_meta.attr("disabled", "disabled");
                $('.tm_meta_serialized').remove();
                tm_meta_serialized=$("<input type='hidden' class='tm_meta_serialized' name='tm_meta_serialized' />").val(data);
                $(this).append(tm_meta_serialized);
                if ($.tmEPOAdmin.did_post_preview==1){
                    tm_meta.attr("disabled", false);
                    $('.tm_meta_serialized').remove();
                    $.tmEPOAdmin.did_post_preview=0;
                }
                return true; // ensure form still submits
            });
            $('#post-preview').on( 'click.post-preview', function( event ) {
                $.tmEPOAdmin.did_post_preview=1;
            });
        },

        init_sections_check: function(){
            var length =$(".builder_wrapper").length;
            if (!length){
                $('.builder_elements').hide();
            }else{
                $('.builder_elements').show();
            }
        },

        fix_content_float: function(){
            var height;
            if ($('.builder_elements').is(':hidden')){
                height=0;
            }else{
                height=$('.builder_elements').outerHeight();
                
            }
            $("#wpcontent").css("margin-bottom", height+"px");
        },

        disable_categories: function(e){
            if ($(".meta-disable-categories").is(":checked")){
                $("#product_catdiv").slideUp();
            }else{
                $("#product_catdiv").slideDown();
            }
        },

        check_section_logic: function(section){
            if (!section && $.tmEPOAdmin.isinit && $.tmEPOAdmin.done_check_section_logic){
                return;
            }
            if (!section){
                section=$('#tmformfieldsbuilderwrap').find("div.builder_wrapper");
            }
            section.each(function(i,el){
                var current_section=$(el);
                var this_section_id=current_section.find('.tm-builder-sections-uniqid').val();
                if (!this_section_id || this_section_id==='' || this_section_id===undefined || this_section_id===false){
                    current_section.find('.tm-builder-sections-uniqid').val($.tm_uniqid("",true));                    
                }
                var this_section_activate_sections_logic=parseInt(current_section.find('.activate-sections-logic').val());
                if (this_section_activate_sections_logic==1){
                    current_section.find('.builder-logic-div').show();
                }else{
                    current_section.find('.builder-logic-div').hide();
                }
            });
            $.tmEPOAdmin.done_check_section_logic=true;
        },

        check_element_logic: function(element){
            if (!element && $.tmEPOAdmin.isinit && $.tmEPOAdmin.done_check_section_logic){
                return;
            }
            var uniqids=[],
                all=false;
            if (!element){
                element=$('#tmformfieldsbuilderwrap').find("div.bitem");
                all=true;
            }
            element.each(function(i,el){
                var this_element_id=$(el).find('.tm-builder-element-uniqid').val();
                if ((all && uniqids.indexOf(this_element_id) !== -1 ) || !this_element_id || this_element_id==='' || this_element_id===undefined || this_element_id===false){
                    $(el).find('.tm-builder-element-uniqid').val($.tm_uniqid("",true));
                }
                if (all){
                    uniqids.push($(el).find('.tm-builder-element-uniqid').val());
                }
                var this_element_activate_element_logic=parseInt($(el).find('.activate-element-logic').val());
                if (this_element_activate_element_logic==1){
                    $(el).find('.builder-logic-div').show();
                }else{
                    $(el).find('.builder-logic-div').hide();
                }
            });
            $.tmEPOAdmin.done_check_section_logic=true;
        },

        section_logic_start: function(section){
            if (!section){
                section=$(".builder_layout .builder_wrapper");
            }
            section.each(function(i,el){
                $.tmEPOAdmin.logic_init($(el));
                try{
                    var rules=$.parseJSON($(el).find(".section_elements .tm-builder-clogic").val());
                    rules=$.tmEPOAdmin.logic_check_section_rules(rules);                
                    $(el).find(".section_elements .tm-builder-clogic").val(JSON.stringify(rules));
                    $(el).find(".section_elements .epo-rule-toggle").val(rules.toggle);
                    $(el).find(".section_elements .epo-rule-what").val(rules.what);
                }catch(err){}
            });
        },

        element_logic_start: function(element){
            if (!element){
                element=$(".builder_layout .builder_wrapper .bitem");
            }
            element.each(function(i,el){
                $.tmEPOAdmin.element_logic_init($(el));
                try{
                    var rules=$.parseJSON($(el).find(".tm-builder-clogic").val());
                    rules=$.tmEPOAdmin.logic_check_element_rules(rules);
                    $(el).find(".tm-builder-clogic").val(JSON.stringify(rules));
                    $(el).find(".epo-rule-toggle").val(rules.toggle);
                    $(el).find(".epo-rule-what").val(rules.what);
                }catch(err){}
            });
        },
        
        panels_reorder:function(obj){
            var panels=$(obj);
            panels.children(".options_wrap").each(function(i,el){
                var tm_default_radio=$(el).find(".tm-default-radio,.tm-default-checkbox");
                tm_default_radio.val(i);
            });
        },

        // Options sortable
        panels_sortable: function(obj) {
            if ($(obj).length==0){
                return;
            }
            obj.not($(".builder_elements .panels_wrap")).sortable({
                cursor: "move",
                tolerance: 'pointer',
                forcePlaceholderSize: true,
                placeholder: 'panel_wrap pl',
                stop: function(e, ui) {
                    $.tmEPOAdmin.panels_reorder($(ui.item).closest(".panels_wrap")); 
                }
            });
        },

        // Delete options button
        builder_panel_delete_onClick: function(e) {
            e.preventDefault();
            $(this).trigger("hideTtooltip");
            var _panels_wrap = $(this).closest(".panels_wrap");
            if (_panels_wrap.children().length > 1) {
                $(this).closest(".options_wrap").css({
                    margin: "0 auto"
                }).animate({
                    opacity: 0,
                    height: 0,
                    width: 0
                }, 300, function() {
                    $(this).remove();
                    _panels_wrap.find(".numorder").each(function(i2, el2) {
                        $(this).html(i2 + 1);
                    });
                    _panels_wrap.children(".options_wrap").each(function(k, v) {
                        $(this).find("[id]").each(function(k2, v2) {
                            var _name = $(this).attr("name").replace(/[\[\]]/g, "");
                            $(this).attr("id", _name + "_" + k);
                        });
                    });
                    $.tmEPOAdmin.panels_reorder(_panels_wrap);
                });
            }
        },

        // Mass add options button
        builder_panel_mass_add_onClick: function(e) {
            e.preventDefault();
            if ($(this).is('.disabled')){
                return;
            }
            $(this).addClass('disabled');
            var $html='<div class="tm-panel-populate-wrapper">'+
            '<textarea class="tm-panel-populate"></textarea>'+
            '<a href="#" class="tm-button button button-primary button-large builder-panel-populate">'+tm_epo_admin.i18n_populate+'</a>'+
            '</div>';
            $(this).after($html);
        },

        // Populate options button
        builder_panel_populate_onClick: function(e) {
            e.preventDefault();
            $(this).remove();
            var lines=$('.tm-panel-populate').val().split(/\n/);
            var texts = [];
            for (var i=0; i < lines.length; i++) {
                // only push this line if it contains a non whitespace character.
                if (/\S/.test(lines[i])) {
                    texts.push($.trim(lines[i]));
                }
            }
            for (var i=0; i < texts.length; i++) {
                var line=texts[i].split('|'); 
                var len=line.length;
                if (len==0){
                    continue;
                }
                if (len==1){
                    line[1]=0;
                }
                line[0]=$.trim(line[0]);
                line[1]=parseFloat($.trim(line[1]));
                if (isNaN(line[1])){
                    line[1]=0;
                }
                $.tmEPOAdmin.add_panel_row(line);
            }
            $('.builder-panel-mass-add').removeClass('disabled');
            $('.tm-panel-populate-wrapper').remove();
        },

        add_panel_row: function(line) {
            var panels_wrap=$('.flasho.tm_wrapper').find('.panels_wrap');
            var _last=panels_wrap.children();
            var _clone = _last.last().tm_clone();
            if (_clone) {
                _clone.find("[name]").val("");
                _clone.find("[id]").each(function(k, v) {
                    var _name = $(this).attr("name").replace(/[\[\]]/g, "");
                    var _l = _last.length;
                    $(this).attr("id", _name + "_" + _l);
                });
                _clone.find(".tm_option_title").val(line[0]);
                _clone.find(".tm_option_value").val(line[0]);
                _clone.find(".tm_option_price").val(line[1]);
                if (line[2]){
                    _clone.find(".tm_option_price_type").val(line[2]);
                    if (_clone.find(".tm_option_price_type").val()==null){
                        _clone.find(".tm_option_price_type").val("");
                    }
                }
                _clone.find(".numorder").html(parseInt(parseInt(_last.length) + 1));                
                _clone.find(".tm_upload_image img").attr("src","");
                _clone.find("input.tm_option_image").val("");
                _clone.find(".tm-default-radio,.tm-default-checkbox").removeAttr("checked").prop("checked",false).val(_last.length);

                panels_wrap.append(_clone);
            }
        },

        // Add options button
        builder_panel_add_onClick: function(e) {
            e.preventDefault();            
            var _last = $(this).prev(".panels_wrap").children();
            var _clone = _last.last().tm_clone();
            if (_clone) {
                _clone.find("[name]").val("");
                _clone.find("[id]").each(function(k, v) {
                    var _name = $(this).attr("name").replace(/[\[\]]/g, "");
                    var _l = _last.length;
                    $(this).attr("id", _name + "_" + _l);
                });
                _clone.find(".numorder").html(parseInt(parseInt(_last.length) + 1));
                var _this = $(this).prev("input");
                _clone.find(".tm_upload_image img").attr("src","");
                _clone.find("input.tm_option_image").val("");
                _clone.find(".tm-default-radio,.tm-default-checkbox").removeAttr("checked").prop("checked",false).val(_last.length);

                $(this).prev(".panels_wrap").append(_clone);
            }
        },

        // Section add button
        builder_add_section_onClick: function(e) {
            if (e) {
                e.preventDefault();
            }
            var _template = $('.builder_hidden_section').data('template');
            if (_template) {
                var _clone = $(_template['html']);
                if (_clone) {
                    _clone.addClass("w100");
                    _clone.addClass("appear");
                    _clone.find('.tm-builder-sections-uniqid').val($.tm_uniqid("",true));
                    _clone.appendTo(".builder_layout");
                    $.tmEPOAdmin.gen_events(_clone);
                    _clone.find(".tm-tabs").tmtabs();
                    $.tmEPOAdmin.check_section_logic(_clone);
                    $.tmEPOAdmin.logic_init(_clone);
                    $.tmEPOAdmin.builder_items_sortable(_clone.find(".bitem_wrapper"));
                    $.tmEPOAdmin.builder_reorder_multiple();
                }
            }
            $.tmEPOAdmin.init_sections_check();
            $.tmEPOAdmin.fix_content_float();
        },

        element_logic_object:{},

        logic_object:{},

        logic_operators:{
            'is':tm_epo_admin.i18n_is,
            'isnot':tm_epo_admin.i18n_is_not,
            'isempty':tm_epo_admin.i18n_is_empty,
            'isnotempty':tm_epo_admin.i18n_is_not_empty
        },

        tm_escape: function(val){            
            return encodeURIComponent(val);
        },

        tm_unescape: function(val){            
            return decodeURIComponent(val);
        },

        element_logic_init: function(el){
            $.tmEPOAdmin.check_section_logic();
            $.tmEPOAdmin.check_element_logic($(el));
            var options=[];
            var logicobj={};
            var sections=$(".builder_layout .builder_wrapper");
            sections.each(function(i,section){
                var section_id=$(section).find('.tm-builder-sections-uniqid').val();
                var fields=$(section).find(".element-radiobuttons,.element-checkboxes,.element-selectbox,.element-textfield,.element-textarea").not($(el));
                var values=[];                               
                // all the fields of current section that can be used as selector in logic
                fields.each(function(ii,field){                    
                    var name=$(field).find('[name^="tm_meta\\[tmfbuilder\\]\\["][name$="_header_title\\]\\[\\]"]');
                    
                    if (name.length==1){
                        var value=name.val();
                        if (value.length==0){
                            value=name.closest(".bitem").find('.label').text();
                        }
                        options.push('<option data-section="'+section_id+'" value="'+$(field).index()+'">'+value+'</option>');
                        
                        if ($(field).is(".element-radiobuttons,.element-checkboxes,.element-selectbox")){

                            var tm_option_titles=$(field).find('.tm_option_title');
                            var tm_option_values=$(field).find('.tm_option_value');
                            var field_values=[];

                            tm_option_titles.each(function(index,title){
                                field_values.push('<option value="'+$.tmEPOAdmin.tm_escape($(tm_option_values[index]).val())+'">'+$(title).val()+'</option>');
                            });

                            values[$(field).index()]='<select data-element="'+$(field).index()+'" data-section="'+section_id+'" class="cpf-logic-value">'+field_values.join('')+'</select>';
                                                      
                        }else{

                            values[$(field).index()]='<input data-element="'+$(field).index()+'" data-section="'+section_id+'" class="cpf-logic-value" type="text" value="">';
                            
                        }
                    }
                });
                
                logicobj[section_id]={
                    'values':values
                }
            });
            if (!$.tmEPOAdmin.element_logic_object.init){
                $.tmEPOAdmin.element_logic_object.init=true;
            }            
            $.tmEPOAdmin.element_logic_object = $.extend( $.tmEPOAdmin.element_logic_object, logicobj );            
            $.tmEPOAdmin.logic_append(el,options);
            
        },

        logic_init: function(el){
            
            $.tmEPOAdmin.check_section_logic($(el));
            var sections=$(el).siblings();
            var options=[];
            var logicobj={};
            
            // every other section
            sections.each(function(i,section){
                $.tmEPOAdmin.check_section_logic($(section));
                var section_id=$(section).find('.tm-builder-sections-uniqid').val();
                
                var fields=$(section).find(".element-radiobuttons,.element-checkboxes,.element-selectbox,.element-textfield,.element-textarea");
                var values=[];
               
                // all the fields of current section that can be used as selector in logic
                fields.each(function(ii,field){
                    
                    var name=$(field).find('[name^="tm_meta\\[tmfbuilder\\]\\["][name$="_header_title\\]\\[\\]"]');
                    
                    if (name.length==1){

                        var value=name.val();
                        if (value.length==0){
                            value=name.closest(".bitem").find('.label').text();
                        }

                        options.push('<option data-section="'+section_id+'" value="'+$(field).index()+'">'+value+'</option>');
                        
                        if ($(field).is(".element-radiobuttons,.element-checkboxes,.element-selectbox")){

                            var tm_option_titles=$(field).find('.tm_option_title');
                            var tm_option_values=$(field).find('.tm_option_value');
                            var field_values=[];
                            
                            tm_option_titles.each(function(index,title){
                                field_values.push('<option value="'+$.tmEPOAdmin.tm_escape($(tm_option_values[index]).val())+'">'+$(title).val()+'</option>');
                            });

                            values[$(field).index()]='<select data-element="'+$(field).index()+'" data-section="'+section_id+'" class="cpf-logic-value">'+field_values.join('')+'</select>';
                           
                        }else{

                            values[$(field).index()]='<input data-element="'+$(field).index()+'" data-section="'+section_id+'" class="cpf-logic-value" type="text" value="">';
                            
                        }

                    }
                });
                
                logicobj[section_id]={
                    'values':values
                }
            });

            if (!$.tmEPOAdmin.logic_object.init){
                $.tmEPOAdmin.logic_object.init=true;
            }
            
            $.tmEPOAdmin.logic_object = $.extend( $.tmEPOAdmin.logic_object, logicobj );

            $.tmEPOAdmin.logic_append(el,options);

        },

        logic_check_section_rules: function(rules){
            if (typeof rules !="object" || rules===null){
                rules={};
            }
            if (!("toggle" in rules)){
                rules.toggle="show";
            }
            if (!("what" in rules)){
                rules.what="any";
            }
            if (!("rules" in rules)){
                rules.rules=[];
            }
            var copy=rules;
            var _logic=$.tmEPOAdmin.logic_object;
            $.each(rules.rules,function(i,_rule){
                var section=_rule.section;
                var element=_rule.element;
                var found= ((section in _logic) && (element in _logic[section].values));
                if (!found){                    
                    delete copy.rules[i];
                }
            });            
            copy.rules=$.tm_array_values(copy.rules);
            return copy;
        },

        logic_check_element_rules: function(rules){
            if (typeof rules !="object" || rules===null){
                rules={};
            }
            if (!("toggle" in rules)){
                rules.toggle="show";
            }
            if (!("what" in rules)){
                rules.what="any";
            }
            if (!("rules" in rules)){
                rules.rules=[];
            }
            var copy=rules;
            var _logic=$.tmEPOAdmin.element_logic_object;
            $.each(rules.rules,function(i,_rule){
                var section=_rule.section;
                var element=_rule.element;
                var found= ((section in _logic) && (element in _logic[section].values));
                if (!found){                    
                    delete copy.rules[i];
                }
            });            
            copy.rules=$.tm_array_values(copy.rules);
            return copy;
        },

        logic_append: function(el,options){
            var obj;
            if ($(el).is(".bitem")){
                obj=$(el).find(".builder_element_wrap");
            }else{
                obj=$(el).find(".section_elements");
            }
            var logic=$(obj).find(".tm-logic-wrapper");
            if (!options || options.length==0){
                logic.html('<div class="errortitle"><p>'+tm_epo_admin.cannot_apply_rules+'</p></div>');
                return false;
            }
            var rules;
            try{
                rules=$.parseJSON($(obj).find(".tm-builder-clogic").val());
                if ($(el).is(".bitem")){
                    rules=$.tmEPOAdmin.logic_check_element_rules(rules);
                }else{
                    rules=$.tmEPOAdmin.logic_check_section_rules(rules);    
                }                
                $(obj).find(".tm-builder-clogic").val(JSON.stringify(rules));

            }catch(err){
            }            
            logic.empty();
            var h='';
            h = '<div class="row nopadding tm-logic-rule">'
                    + '<div class="cell col-4 tm-logic-element">'
                    + '</div>'                    
                    + '<div class="cell col-2 tm-logic-operator">'
                    + '</div>'                        
                    + '<div class="cell col-4 tm-logic-value">'
                    + '</div>'
                    + '<div class="cell col-2 tm-logic-func">'
                    + '<a class="button button-secondary button-small cpf-add-rule" href="#cpf-add-rule"><i class="fa fa-plus"></i></a>'
                    + ' <a class="button button-secondary button-small cpf-delete-rule" href="#cpf-delete-rule"><i class="fa fa-times"></i></a>'
                    + '</div>'
                + '</div>';
            var rule=$(h);

            var tm_logic_element=$('<select class="cpf-logic-element">'+options.join('')+'</select>');
            rule.find('.tm-logic-element').append(tm_logic_element);
            
            var operators='';
            for (var o in $.tmEPOAdmin.logic_operators){
                operators=operators+'<option value="'+o+'">'+$.tmEPOAdmin.logic_operators[o]+'</option>';
            }
            operators=$('<select class="cpf-logic-operator">'+operators+'</select>');
            rule.find('.tm-logic-operator').append(operators);
            
            if (!rules || !('rules' in rules) || !rules.rules.length){
                rule.appendTo(logic).find('.cpf-logic-element').trigger('change.cpf',[$(el).is(".bitem")]);
                rule.appendTo(logic).find('.cpf-logic-operator').trigger('change.cpf',[$(el).is(".bitem")]);                
            }else{
                $.each(rules.rules,function(i,_rule){
                    var current_rule=rule.clone();
                    current_rule.appendTo(logic);
                    current_rule.find('.cpf-logic-element').find('option[data-section="'+_rule.section+'"][value="'+_rule.element+'"]')[0].selected = true;
                    current_rule.find('.cpf-logic-element').trigger('change.cpf',[$(el).is(".bitem")]);
                    current_rule.find('.cpf-logic-operator').val(_rule.operator);
                    current_rule.find('.cpf-logic-operator').trigger('change.cpf',[$(el).is(".bitem")]);
                    current_rule.find('.cpf-logic-value').val($.tmEPOAdmin.tm_escape($.tmEPOAdmin.tm_unescape(_rule.value)));
                });
            }
        },

        logic_get_JSON: function(s){
            var rules=$(s).find(".builder-logic-div");
            var this_section_id=s.find('.tm-builder-sections-uniqid').val();
            var section_logic={};
            var _toggle=rules.find(".epo-rule-toggle").val();
            var _what=rules.find(".epo-rule-what").val();
            section_logic.section=this_section_id;
            section_logic.toggle=_toggle;
            section_logic.what=_what;
            section_logic.rules=[];
            rules.find(".tm-logic-wrapper").children(".tm-logic-rule").each(function(i,el){
                var cpf_logic_section=$(el).find(".cpf-logic-element").children("option:selected").attr('data-section');
                var cpf_logic_element=$(el).find(".cpf-logic-element").val();
                var cpf_logic_operator=$(el).find(".cpf-logic-operator").val();
                var cpf_logic_value=$(el).find(".cpf-logic-value").val();

                section_logic.rules.push({
                    "section":cpf_logic_section,
                    "element":cpf_logic_element,
                    "operator":cpf_logic_operator,
                    "value":cpf_logic_value
                });
                
            });
            return JSON.stringify(section_logic);
        },

        element_logic_get_JSON: function(s){
            var rules=$(s).find(".builder-logic-div");
            var this_element_id=s.find('.tm-builder-element-uniqid').val();
            var element_logic={};
            var _toggle=rules.find(".epo-rule-toggle").val();
            var _what=rules.find(".epo-rule-what").val();
            element_logic.element=this_element_id;
            element_logic.toggle=_toggle;
            element_logic.what=_what;
            element_logic.rules=[];
            rules.find(".tm-logic-wrapper").children(".tm-logic-rule").each(function(i,el){
                var cpf_logic_section=$(el).find(".cpf-logic-element").children("option:selected").attr('data-section');
                var cpf_logic_element=$(el).find(".cpf-logic-element").val();
                var cpf_logic_operator=$(el).find(".cpf-logic-operator").val();
                var cpf_logic_value=$(el).find(".cpf-logic-value").val();

                element_logic.rules.push({
                    "section":cpf_logic_section,
                    "element":cpf_logic_element,
                    "operator":cpf_logic_operator,
                    "value":cpf_logic_value
                });
                
            });
            return JSON.stringify(element_logic);
        },

        cpf_add_rule: function(e) {
            e.preventDefault();            
            var _last = $(this).closest(".tm-logic-rule");
            var _clone = _last.tm_clone(true);
            if (_clone) {
                _last.after(_clone);
            }
        },

        cpf_delete_rule: function(e) {
            e.preventDefault();
            $(this).trigger("hideTtooltip");
            var _wrapper = $(this).closest(".tm-logic-wrapper");
            if (_wrapper.children().length > 1) {
                $(this).closest(".tm-logic-rule").css({
                    margin: "0 auto"
                }).animate({
                    opacity: 0,
                    height: 0,
                    width: 0
                }, 300, function() {
                    $(this).remove();                   
                });
            }
        },

        builder_add_on_section_onClick: function(e) {
            e.preventDefault();
            if (!$(this).data("inserted")){
                var el=$(".builder_drag_elements").tm_clone().addClass("float").insertAfter($(this));
                $(this).data("inserted",1);
                $(this).closest('.builder_wrapper').css('zIndex',2);
            }else{
                $(this).closest(".builder_wrapper").find(".float.builder_drag_elements").remove();
                $(this).data("inserted",0);
                $(this).closest('.builder_wrapper').css('zIndex','');
            }
            

        },

        builder_float_add_onClick: function(e) {
            e.preventDefault();
            var el=$(this).attr("data-element"),
                wr=$(this).closest(".builder_wrapper");
            $(this).closest(".builder_wrapper").find(".builder_add_on_section").data("inserted",0);
            $(this).closest(".builder_wrapper").find(".float.builder_drag_elements").remove();
            $.tmEPOAdmin.builder_clone_element(el, wr);
            $.tmEPOAdmin.logic_reindex();
        },

        // Add Element button
        builder_add_onClick: function(e) {
            e.preventDefault();
            $.tmEPOAdmin.builder_clone_element($("#builder_items option:selected").val(), ".builder_wrapper:last");
        },

        builder_items_sortable_obj:{
            "start":{},
            "end":{}
        },

        section_logic_reindex: function(){
            var l=$.tmEPOAdmin.builder_items_sortable_obj;
            $(".builder_layout .builder_wrapper").each(function(i,el){

                var obj=$(el).find(".section_elements");            
                var section_eq=$(el).index();
                var copy_rules=[];

                var section_rules=$.parseJSON($(obj).find(".tm-builder-clogic").val());

                if (!(section_rules && ("rules" in section_rules) && section_rules["rules"].length>0)){
                        
                    return true; // skip 
                }
                        
                // Element is dragged on this section
                if (l.end.section_eq==section_eq){
                            
                    // Getting here means that an element from another section
                    // is being dragged on this section
                    $.each(section_rules["rules"],function(i,rule){
                        var copy=rule;
                        if (rule.element==l.start.element){
                            // delete rule on this element                                    
                        }
                        else if (rule.element>l.start.element){
                            copy.element=parseInt(copy.element)-1;
                            copy_rules[i]=copy;
                        }
                        else{
                            copy_rules[i]=copy;
                        }                               
                    });
                    copy_rules=$.tm_array_values(copy_rules);
                    if (copy_rules.length==0){
                        $(obj).find(".activate-sections-logic").val("").trigger("change.cpf");
                    }
                    section_rules["rules"]=copy_rules;
                    $(obj).find(".tm-builder-clogic").val(JSON.stringify(section_rules));

                // Element is not dragged on this section
                }else{

                    // Getting here means that an element from another section
                    // is being dragged on another section that is not the current section
                    $.each(section_rules["rules"],function(i,rule){
                        var copy=rule;

                        // Element is not changing sections
                        if (rule.section==l.start.section && rule.section==l.end.section){
                            // Element belonging to a rule is being dragged
                            if (rule.element==l.start.element){
                                copy.section=l.end.section;
                                copy.element=l.end.element;
                            }
                            // Element not belonging to a rule is being dragged
                            // and breaks the rule
                            else if (rule.element>l.start.element && rule.element<=l.end.element){
                                        
                                copy.element=parseInt(copy.element)-1;
                            }
                            else if (rule.element<l.start.element && rule.element>=l.end.element){
                                        
                                copy.element=parseInt(copy.element)+1;
                            }
                        }
                        // Element is getting dragged off this section
                        else if (rule.section==l.start.section && rule.section!=l.end.section){
                            // Element belonging to a rule is being dragged
                            if (rule.element==l.start.element){
                                copy.section=l.end.section;
                                copy.element=l.end.element;
                            }
                            // Element not belonging to a rule is being dragged
                            // and breaks the rule
                            else if (rule.element>l.start.element){
                                copy.element=parseInt(copy.element)-1;
                            }
                        }
                        // Element is getting dragged on this section
                        else if (rule.section!=l.start.section && rule.section==l.end.section){
                            if (rule.element>=l.end.element){
                                copy.element=parseInt(copy.element)+1;
                            }
                        }
                        if (l.end.section=="delete" && copy.element=="delete"){
                            // rule needs to be deleted                           
                        }else{
                            copy_rules[i]=copy;
                        }
                    });
                    copy_rules=$.tm_array_values(copy_rules);
                    if (copy_rules.length==0){
                        $(obj).find(".activate-sections-logic").val("").trigger("change.cpf");
                    }
                    section_rules["rules"]=copy_rules;
                    $(obj).find(".tm-builder-clogic").val(JSON.stringify(section_rules));

                }
            });
        },
        
        element_logic_reindex: function(){
            var l=$.tmEPOAdmin.builder_items_sortable_obj;
            $(".bitem").each(function(i,el){

                var obj=$(el).find(".builder_element_wrap");           
                var copy_rules=[];
                var element_rules=$.parseJSON($(obj).find(".tm-builder-clogic").val());

                if (!(element_rules && ("rules" in element_rules) && element_rules["rules"].length>0)){
                        
                    return true; // skip 
                }

                $.each(element_rules["rules"],function(i,rule){
                    var copy=rule;
                    // Element is not changing sections
                    if (rule.section==l.start.section && rule.section==l.end.section){
                         // Element belonging to a rule is being dragged
                        if (rule.element==l.start.element){
                            //copy.section=l.end.section;
                            copy.element=l.end.element;
                        }
                        // Element not belonging to a rule is being dragged
                        // and breaks the rule
                        else if (rule.element>l.start.element && rule.element<=l.end.element){                                        
                            copy.element=parseInt(copy.element)-1;
                        }
                        else if (rule.element<l.start.element && rule.element>=l.end.element){                                        
                            copy.element=parseInt(copy.element)+1;
                        }
                    }
                    // Element is getting dragged off its section
                    else if (rule.section==l.start.section && rule.section!=l.end.section){
                        // Element belonging to a rule is being dragged
                        if (rule.element==l.start.element){
                            copy.section=l.end.section;
                            copy.element=l.end.element;
                        }
                        // Element not belonging to a rule is being dragged
                        // and breaks the rule
                        else if (rule.element>l.start.element){
                            copy.element=parseInt(copy.element)-1;
                        }  
                    }
                    // Element is getting dragged on this rule's section
                    else if (rule.section!=l.start.section && rule.section==l.end.section){
                        if (rule.element>=l.end.element){
                           copy.element=parseInt(copy.element)+1;
                        }
                    }
                    if (l.end.section=="delete" && copy.element=="delete"){
                        // rule needs to be deleted                           
                    }else{
                        copy_rules[i]=copy;
                    }
                });
                copy_rules=$.tm_array_values(copy_rules);
                if (copy_rules.length==0){
                    $(obj).find(".activate-element-logic").val("").trigger("change.cpf");
                }
                element_rules["rules"]=copy_rules;
                $(obj).find(".tm-builder-clogic").val(JSON.stringify(element_rules));
            });
        },

        logic_reindex: function(){
            var l=$.tmEPOAdmin.builder_items_sortable_obj;
            if (l.start.section==l.end.section && l.start.section_eq==l.end.section_eq && l.start.element==l.end.element){
                // Getting here means that dragging did not occur
            }else{
                $.tmEPOAdmin.section_logic_reindex();
                $.tmEPOAdmin.element_logic_reindex();                
            }
            $.tmEPOAdmin.builder_items_sortable_obj={"start":{},"end":{}};
        },

        // Elements sortable
        builder_items_sortable: function(obj) {
            obj.sortable({
                handle: ".move,.label,.label-icon",
                cursor: "move",
                items: ".bitem",
                start: function(e, ui) {

                    if (!$(ui.item).hasClass("ditem")) {
                        $(ui.item).closest(".builder_wrapper").find(".tm_builder_sections").val(function(i, oldval) {
                            return --oldval;
                        });
                        $.tmEPOAdmin.builder_items_sortable_obj["start"].section=$(ui.item).closest(".builder_wrapper").find(".tm-builder-sections-uniqid").val();
                        $.tmEPOAdmin.builder_items_sortable_obj["start"].section_eq=$(ui.item).closest(".builder_wrapper").index();
                        $.tmEPOAdmin.builder_items_sortable_obj["start"].element=$(ui.item).index();                        
                    }else{
                        $.tmEPOAdmin.builder_items_sortable_obj["start"].section="drag";
                        $.tmEPOAdmin.builder_items_sortable_obj["start"].section_eq="drag";
                        $.tmEPOAdmin.builder_items_sortable_obj["start"].element="drag";
                    }

                    $(".builder_layout .bitem_wrapper").addClass("highlight");
                },
                stop: function(e, ui) {

                    if (!$(ui.item).hasClass("ditem")) {
                        $(ui.item).closest(".builder_wrapper").find(".tm_builder_sections").val(function(i, oldval) {
                            return ++oldval;
                        });
                    }
                    $.tmEPOAdmin.builder_items_sortable_obj["end"].section=$(ui.item).closest(".builder_wrapper").find(".tm-builder-sections-uniqid").val();
                    $.tmEPOAdmin.builder_items_sortable_obj["end"].section_eq=$(ui.item).closest(".builder_wrapper").index();
                    $.tmEPOAdmin.builder_items_sortable_obj["end"].element=$(ui.item).index();
                    
                    $.tmEPOAdmin.builder_reorder_multiple();
                    if ($(ui.item).hasClass("ditem")) {
                        ui.draggable = ui.item;
                        $.tmEPOAdmin.drag_drop(e, ui, $(this));
                    }
                    $.tmEPOAdmin.logic_reindex();
                    $(".builder_layout .bitem_wrapper").removeClass("highlight");

                },
                tolerance: 'pointer',
                forcePlaceholderSize: true,
                placeholder: {
                    element: function(clone, ui) {
                        return $('<div class="bitem pl"><span></span></div>');
                    },
                    update: function() {
                        return;
                    }
                },
                cancel: '.panels_wrap',
                connectWith: '.builder_wrapper .bitem_wrapper'
            });
        },

        // Element delete button
        builder_delete_onClick: function() {
            if (confirm(tm_epo_admin.builder_delete)) {
                var _bitem=$(this).closest(".bitem");
                $(this).closest(".builder_wrapper").find(".tm_builder_sections").val(function(i, oldval) {
                    return --oldval;
                });
                
                $.tmEPOAdmin.builder_items_sortable_obj["start"].section=_bitem.closest(".builder_wrapper").find(".tm-builder-sections-uniqid").val();
                $.tmEPOAdmin.builder_items_sortable_obj["start"].section_eq=_bitem.closest(".builder_wrapper").index();
                $.tmEPOAdmin.builder_items_sortable_obj["start"].element=_bitem.index();

                $.tmEPOAdmin.builder_items_sortable_obj["end"].section="delete";
                $.tmEPOAdmin.builder_items_sortable_obj["end"].section_eq="delete";
                $.tmEPOAdmin.builder_items_sortable_obj["end"].element="delete";
                $(this).closest(".bitem").remove();
                $.tmEPOAdmin.logic_reindex();
                
                $.tmEPOAdmin.builder_reorder_multiple();
            }
        },

        builder_section_fold_onClick: function(e) {
            var builder_wrapper=$(this).closest(".builder_wrapper"),
                bitem_wrapper=builder_wrapper.find(".bitem_wrapper");
            if (!$(this).data("folded")){
                $(this).data("folded",true);
                bitem_wrapper.hide();
                $(this).removeClass("fa-caret-down").addClass("fa-caret-up");
            }else{
                $(this).data("folded",false);
                bitem_wrapper.show();
                $(this).removeClass("fa-caret-up").addClass("fa-caret-down");
            }
            $(this).closest(".builder_wrapper").find(".builder_add_on_section").data("inserted",0);
            $(this).closest(".builder_wrapper").find(".float.builder_drag_elements").remove();            
        },

        // Section delete button
        builder_section_delete_onClick: function() {
            if (confirm(tm_epo_admin.builder_delete)) {
                $(this).closest(".builder_wrapper").remove();
                $.tmEPOAdmin.builder_reorder_multiple();                
                $(".builder_layout .builder_wrapper").each(function(i,el){
                    $.tmEPOAdmin.logic_init($(el));
                });
                $.tmEPOAdmin.init_sections_check();
                $.tmEPOAdmin.fix_content_float();
            }
        },

        // Element plus button
        builder_plus_onClick: function() {
            var s = $.tmEPOAdmin.builder_size();
            var current_size = $(this).parentsUntil(".bitem").parent();
            var x;
            for (x in s) {
                if (current_size.hasClass(s[x][0])) {
                    if (x < 5) {
                        current_size.removeClass("" + s[x][0]);
                        current_size.addClass("" + s[parseInt(parseInt(x) + 1)][0]);
                        current_size.find(".size").text(s[parseInt(parseInt(x) + 1)][1]);
                        current_size.find(".div_size").val(s[parseInt(parseInt(x) + 1)][0]);
                    }
                    break;
                }
            }
        },

        // Element minus button
        builder_minus_onClick: function() {
            var s = $.tmEPOAdmin.builder_size();
            var current_size = $(this).parentsUntil(".bitem").parent();
            var x;
            for (x in s) {
                if (current_size.hasClass(s[x][0])) {
                    if (x > 0) {
                        current_size.removeClass("" + s[x][0]);
                        current_size.addClass("" + s[parseInt(parseInt(x) - 1)][0]);
                        current_size.find(".size").text(s[parseInt(parseInt(x) - 1)][1]);
                        current_size.find(".div_size").val(s[parseInt(parseInt(x) - 1)][0]);
                    }
                    break;
                }
            }
        },

        // Section plus button
        builder_section_plus_onClick: function() {
            var s = $.tmEPOAdmin.builder_size();
            var current_size = $(this).closest(".builder_wrapper");
            var x;
            for (x in s) {
                if (current_size.hasClass(s[x][0])) {
                    if (x < 5) {
                        current_size.removeClass("" + s[x][0]);
                        current_size.addClass("" + s[parseInt(parseInt(x) + 1)][0]);
                        current_size.find(".btitle .size").text(s[parseInt(parseInt(x) + 1)][1]);
                        current_size.find(".tm_builder_sections_size").val(s[parseInt(parseInt(x) + 1)][0]);
                    }
                    break;
                }
            }
        },

        // Section minus button
        builder_section_minus_onClick: function() {
            var s = $.tmEPOAdmin.builder_size();
            var current_size = $(this).closest(".builder_wrapper");
            var x;
            for (x in s) {
                if (current_size.hasClass(s[x][0])) {
                    if (x > 0) {
                        current_size.removeClass("" + s[x][0]);
                        current_size.addClass("" + s[parseInt(parseInt(x) - 1)][0]);
                        current_size.find(".btitle .size").text(s[parseInt(parseInt(x) - 1)][1]);
                        current_size.find(".tm_builder_sections_size").val(s[parseInt(parseInt(x) - 1)][0]);
                    }
                    break;
                }
            }
        },

        // Section edit button
        builder_section_item_onClick: function() {
            var _bs = $(this).closest(".builder_wrapper");
            $.tmEPOAdmin.gen_events(_bs);            
            $.tmEPOAdmin.check_section_logic(_bs);
            var _current_logic=$.tmEPOAdmin.logic_object;             
            $.tmEPOAdmin.logic_init(_bs);
            var _s = $(this).closest(".builder_wrapper").find(".section_elements");
            var _c = _s.tm_clone();
            var $_html = $.tmEPOAdmin.builder_floatbox_template({
                "id": "temp_for_floatbox_insert",
                "html": "",
                "title": tm_epo_admin.edit_settings
            });

            var _to = $("body").addClass("overflow").tm_floatbox({
                "fps": 1,
                "ismodal": true,
                "refresh": 100,
                "width": "80%",
                "height": "80%",
                "classname": "flasho tm_wrapper",
                "data": $_html
            });
            var clicked=false;
            $(".details_cancel").click(function() {
                if (clicked){
                    return;
                }
                clicked=true;
                $.tmEPOAdmin.logic_object=_current_logic;
                $.tmEPOAdmin.removeTinyMCE('.flasho.tm_wrapper');
                _c.prependTo(_bs).addClass("closed");
                $.tmEPOAdmin.builder_clone_after_events(_c);
                $("body").removeClass("overflow");
                if (_to) _to.cancelfunc();
            });
            $(".details_update").click(function() {
                if (clicked){
                    return;
                }
                clicked=true;
                $.tmEPOAdmin.removeTinyMCE('.flasho.tm_wrapper');
                _s.find(".tm-builder-clogic").val($.tmEPOAdmin.logic_get_JSON(_s));
                _s.prependTo(_bs).addClass("closed");
                $.tmEPOAdmin.builder_clone_after_events(_s);
                $("body").removeClass("overflow");

                if (_to) _to.cancelfunc();
            });
            _s.appendTo("#temp_for_floatbox_insert").removeClass("closed");
            $.tmEPOAdmin.addTinyMCE('.flasho.tm_wrapper');            
        },

        // Element edit button
        builder_item_onClick: function() {
            var bitem=$(this).closest(".bitem");
            $.tmEPOAdmin.panels_sortable(bitem.find(".panels_wrap"));
            $.tmEPOAdmin.gen_events(bitem);  
            $.tmEPOAdmin.check_element_logic(bitem);
            var _current_logic=$.tmEPOAdmin.element_logic_object;             
            $.tmEPOAdmin.element_logic_init(bitem);
            var _bs = $(this).closest(".hstc2");
            var _s = $(this).closest(".hstc2").find(".inside:first");
            var _c = _s.tm_clone();
            var $_html = $.tmEPOAdmin.builder_floatbox_template({
                "id": "temp_for_floatbox_insert",
                "html": "",
                "title": tm_epo_admin.edit_settings
            });
            var _to = $("body").addClass("overflow").tm_floatbox({
                "fps": 1,
                "ismodal": true,
                "refresh": 100,
                "width": "80%",
                "height": "80%",
                "classname": "flasho tm_wrapper",
                "data": $_html
            });
            var clicked=false;
            $(".details_cancel").click(function() {
                if (clicked){
                    return;
                }
                clicked=true;
                $.tmEPOAdmin.element_logic_object=_current_logic;
                $.tmEPOAdmin.removeTinyMCE('.flasho.tm_wrapper');
                _c.appendTo(_bs);
                _c = _c.parentsUntil(".bitem").parent();
                $.tmEPOAdmin.builder_clone_after_events(_c);
                $("body").removeClass("overflow");
                if (_to) _to.cancelfunc();
            });
            $(".details_update").click(function() {
                if (clicked){
                    return;
                }
                clicked=true;
                $.tmEPOAdmin.removeTinyMCE('.flasho.tm_wrapper');
                 _s.find(".tm-builder-clogic").val($.tmEPOAdmin.element_logic_get_JSON(_s));
                _s.appendTo(_bs);
                $("body").removeClass("overflow");
                $.tmEPOAdmin.builder_clone_after_events(_s);
                _s.find(".tm-header-title").trigger("changetitle.cpf");
                if (_to) _to.cancelfunc();
            });
            _s.appendTo("#temp_for_floatbox_insert");
            $.tmEPOAdmin.tm_upload();
            $.tmEPOAdmin.tm_url();
            $.tmEPOAdmin.addTinyMCE('.flasho.tm_wrapper');            
        },

        // Add Element draggable to sortable
        drag_drop: function(event, ui, dropable) {
            var selected_element = $(ui.draggable).attr('class').split(/\s+/).filter(function(item) {
                return item.indexOf("element-") === -1 ? "" : item;
            }).toString();
            selected_element = selected_element.replace(/element-/gi, "");
            if (selected_element){
                $.tmEPOAdmin.builder_clone_element(selected_element, dropable.closest(".builder_wrapper"));
            }
        },

        // Add Element to sortable via Add button
        builder_clone_element: function(element, wrapper_selector) {
            var _template = $('.builder_hidden_elements').data('template');
            if (!_template) {
                return;
            }
            var _clone = $(_template['html']).filter(".bitem.element-" + element).tm_clone(true);
            if (_clone) {
                _clone.find('.tm-builder-element-uniqid').val($.tm_uniqid("",true));
                if ($(".builder_wrapper").length <= 0) {
                    $.tmEPOAdmin.builder_add_section_onClick();
                }
                _clone.addClass("appear");
                $.tmEPOAdmin.set_field_title(_clone);
                if ($(wrapper_selector).find(".bitem_wrapper").find(".ditem").length > 0) {
                    $(wrapper_selector).find(".bitem_wrapper").find(".ditem").replaceWith(_clone);
                } else {
                    $(wrapper_selector).find(".bitem_wrapper").append(_clone);
                }
                $(wrapper_selector).find(".tm_builder_sections").val(function(i, oldval) {
                    return ++oldval;
                });
                $.tmEPOAdmin.gen_events(_clone);
                _clone.find(".tm-tabs").tmtabs();
                _clone.find(".tm-header-title").data("id",_clone);
                $.tmEPOAdmin.panels_sortable(_clone.find(".panels_wrap"));
                $.tmEPOAdmin.check_element_logic(_clone);
                $.tmEPOAdmin.builder_reorder_multiple();
            }
        },

        // Element clone button
        builder_clone_onClick: function(e) {
            e.preventDefault();
            if (!confirm(tm_epo_admin.builder_clone)) return;
            var _bitem = $(this).closest(".bitem");
            var _label_data=_bitem.data("original_title");
            var _clone = _bitem.tm_clone();
            _clone.data("original_title",_label_data);
            var _class = $(this).closest(".bitem").attr('class').split(' ')
                .map(function(cls) {
                    if (cls.indexOf("element-", 0) !== -1) {
                        return cls;
                    }
                })
                .filter(function(v, k, el) {
                    if (v !== null && v !== undefined) {
                        return v;
                    }
                });
            if (_clone) {
                _bitem.after(_clone);
                _clone.closest(".builder_wrapper").find(".tm_builder_sections").val(function(i, oldval) {
                    return ++oldval;
                });
                _clone.find(".tm-header-title").data("id",_clone);
                _clone.find('.tm-builder-element-uniqid').val($.tm_uniqid("",true));
                $.tmEPOAdmin.builder_clone_after_events(_clone);
                $.tmEPOAdmin.builder_reorder_multiple();
                $.tmEPOAdmin.builder_items_sortable_obj["start"].section="clone";
                $.tmEPOAdmin.builder_items_sortable_obj["start"].section_eq="clone";
                $.tmEPOAdmin.builder_items_sortable_obj["start"].element="clone";
                $.tmEPOAdmin.builder_items_sortable_obj["end"].section=_bitem.closest(".builder_wrapper").find(".tm-builder-sections-uniqid").val();
                $.tmEPOAdmin.builder_items_sortable_obj["end"].section_eq=_bitem.closest(".builder_wrapper").index();
                $.tmEPOAdmin.builder_items_sortable_obj["end"].element=_clone.index();
                $.tmEPOAdmin.logic_reindex();
            }
        },

        // Section clone button
        builder_section_clone_onClick: function(e) {
            e.preventDefault();
            if (!confirm(tm_epo_admin.builder_clone)) return;
            var _bitem = $(this).closest(".builder_wrapper");
            var _clone = _bitem.tm_clone();
            if (_clone) {
                _clone.find('.tm-builder-sections-uniqid').val($.tm_uniqid("",true));
                var original_titles=[];
                _bitem.find(".bitem").each(function(i,el){
                    original_titles[i]=$(el).data("original_title"); 
                });
                _bitem.after(_clone);
                _clone.find(".bitem").each(function(i,el){
                    $(el).data("original_title", original_titles[i]); 
                    $(el).find(".tm-header-title").data("id",$(el)); 
                });
                $.tmEPOAdmin.builder_reorder_multiple();
                $.tmEPOAdmin.builder_items_sortable(_clone.find(".bitem_wrapper"));
                $.tmEPOAdmin.builder_clone_after_events(_clone);
                _clone.find('.tm-builder-sections-uniqid').val($.tm_uniqid("",true));
                $.tmEPOAdmin.check_section_logic(_clone);
                $.tmEPOAdmin.check_element_logic();
                $.tmEPOAdmin.logic_init(_clone);
                _clone.addClass("appear");
            }
        },

        // Helper : Holds element and sections available sizes
        builder_size: function() {
            var s = [];
            s[0] = ['w25', '1/4'];
            s[1] = ['w33', '1/3'];
            s[2] = ['w50', '1/2'];
            s[3] = ['w66', '2/3'];
            s[4] = ['w75', '3/4'];
            s[5] = ['w100', '1/1'];
            return s;
        },

        // Helper : Creates the html for the edit pop up
        builder_floatbox_template: function(data) {
            var out = '';
            out = "<div class=\'header\'><h3>" + data.title + "<\/h3><\/div>" +
                "<div id=\'" + data.id + "\' class=\'float_editbox\'>" +
                data.html + "<\/div>" +
                "<div class=\'footer\'><div class=\'inner\'><span class=\'tm-button button button-primary button-large details_update\'>" +
                tm_epo_admin.update +
                "<\/span>&nbsp;<span class=\'tm-button button button-secondary button-large details_cancel\'>" +
                tm_epo_admin.i18n_cancel +
                "<\/span><\/div><\/div>";
            return out;
        },

        builder_floatbox_template_import: function(data) {
            var out = '';
            out = "<div class=\'header\'><h3>" + data.title + "<\/h3><\/div>" +
                "<div id=\'" + data.id + "\' class=\'float_editbox\'>" +
                data.html + "<\/div>" +
                "<div class=\'footer\'><div class=\'inner\'><span class=\'tm-button button button-secondary button-large details_cancel\'>" +
                tm_epo_admin.i18n_cancel +
                "<\/span><\/div><\/div>";
            return out;
        },

        // Helper : Renames all fields that contain multiple options
        builder_reorder_multiple: function() {
            var obj;
            var inputArray = $(".builder_layout").find('[name^="tm_meta\\[tmfbuilder\\]\\[multiple_"]').map(function() {
                return $(this).closest(".bitem").attr('class').split(' ')
                    .map(function(cls) {
                        if (cls.indexOf("element-", 0) !== -1) {
                            return cls;
                        }
                    })
                    .filter(function(v, k, el) {
                        if (v !== null && v !== undefined) {
                            return v;
                        }
                    });
            }).toArray();
            var outputArray = [];
            for (var i = 0; i < inputArray.length; i++) {
                if ((jQuery.inArray(inputArray[i], outputArray)) == -1) {
                    outputArray.push(inputArray[i]);
                }
            }
            var id_array = {};
            for (var key in outputArray) {
                obj = $(".builder_layout ." + outputArray[key]);
                obj.each(
                    function(i, el) {
                        $(el).find(".tm-default-radio").each(function(index,element){
                            var _m = $(element).attr('name');
                            var __m = /\[[0-9]+\]\[\]/g;
                            var __m2 = /\[[0-9]+\]/g;
                            if (_m.match(__m) != null) {
                                _m = _m.replace(__m, "[" + i + "][]");
                            } else {
                                if (_m.match(__m2) != null) {
                                    _m = _m.replace(__m2, "[" + i + "]");
                                }
                            }
                            var _name = _m.replace(/[\[\]]/g, "");
                            if (_name in id_array) {
                                id_array[_name] = parseInt(id_array[_name]) + 1;
                            } else {
                                id_array[_name] = 0;
                            }                            
                            var _check=false;
                            if ($(element).is(":radio:checked")){
                                _check=true;                                
                            }
                             _m = _m+ '_temp';
                            $(element).attr('name', _m);
                            if (_check){
                                $(element).attr("checked","checked").prop("checked",true);
                            }else{
                                $(element).removeAttr("checked").prop("checked",false);
                            }
                        });
                        
                        $(el).find("[name]").not(".tm-default-radio").attr('name', function() {
                            var _m = $(this).attr('name');
                            var __m = /\[[0-9]+\]\[\]/g;
                            var __m2 = /\[[0-9]+\]/g;
                            if (_m.match(__m) != null) {
                                _m = _m.replace(__m, "[" + i + "][]");
                            } else {
                                if (_m.match(__m2) != null) {
                                    _m = _m.replace(__m2, "[" + i + "]");
                                }
                            }
                            var _name = _m.replace(/[\[\]]/g, "");
                            if (_name in id_array) {
                                id_array[_name] = parseInt(id_array[_name]) + 1;
                            } else {
                                id_array[_name] = 0;
                            }
                            $(this).attr('id', _name + "_" + id_array[_name]);
                            return _m;
                        });
                    }
                );
            }

            /* preserving checked radios */
            $(".builder_layout").find(".tm-default-radio").each(function(index,element){
                var _n = $(element).attr('name');
                _n=_n.replace(/_temp/g, "");
                $(this).attr('name',_n);
            });

            obj = $(".builder_layout").find('[name]').not('[name^="tm_meta\\[tmfbuilder\\]\\[multiple_"]');
            obj.each(
                function(i, el) {
                    var _name = $(this).attr('name').replace(/[\[\]]/g, "");
                    if (_name in id_array) {
                        id_array[_name] = parseInt(id_array[_name]) + 1;
                    } else {
                        id_array[_name] = 0;
                    }
                    $(el).attr('id', _name + id_array[_name]);
                }
            );$.tmEPOAdmin.id_array=id_array;
        },

        // Helper : Generates new event after cloning an element
        builder_clone_after_events: function(_clone) {
            _clone.find("input.tm-color-picker.minicolors-input").minicolors("destroy").minicolors();
            _clone.find("input.tm-color-picker-t.minicolors-input").minicolors("destroy").minicolors({
                'transparent': true
            });
            $.tmEPOAdmin.panels_sortable(_clone.find(".panels_wrap"));
            _clone.find(".tm-tabs").tmtabs();
        },

        // Helper : Generates general events
        gen_events: function(obj) {
            if (!obj) {
                obj = $(".builder_layout ");
            }
            obj.find("input.tm-color-picker").minicolors();
            obj.find("input.tm-color-picker-t").minicolors({
                'transparent': true
            });
        },

        addTinyMCE: function(element) {
            if (!$(element)) {
                return;
            }
            var getter_tmce = 'excerpt';
            var tmc_defaults = {
                theme: 'modern',
                menubar: false,
                wpautop: true,
                indent: false,
                toolbar1: 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink,fullscreen',
                plugins: 'fullscreen,image,wordpress,wpeditimage,wplink'
            };
            var qt_defaults = {
                buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,fullscreen'
            };
            var init_settings = ((typeof tinyMCEPreInit == 'object') && ('mceInit' in tinyMCEPreInit) && (getter_tmce in tinyMCEPreInit.mceInit)) ? tinyMCEPreInit.mceInit[getter_tmce] : tmc_defaults;
            var qt_settings = ((typeof tinyMCEPreInit == 'object') && ('qtInit' in tinyMCEPreInit) && (getter_tmce in tinyMCEPreInit.qtInit)) ? tinyMCEPreInit.qtInit[getter_tmce] : qt_defaults;
            var tmc_settings, id, tqt_settings;
            var editor_tools_html = $('#wp-' + getter_tmce + '-editor-tools').html();
            var editor_tools_class = $('#wp-' + getter_tmce + '-editor-tools').attr('class');
            $(element).find('textarea').each(function(i, textarea) {
                id = $(textarea).attr('id');
                if (id) {
                    tmc_settings = $.extend({}, init_settings, {
                        selector: "#" + id
                    });
                    tqt_settings = $.extend({}, qt_settings, {
                        id: id
                    });
                    if (typeof tinyMCEPreInit == 'object') {
                        tinyMCEPreInit.mceInit[id] = tmc_settings;
                        tinyMCEPreInit.qtInit[id] = tqt_settings;
                    }
                    $(textarea).addClass("wp-editor-area").wrap('<div id="wp-' + id + '-wrap" class="wp-core-ui wp-editor-wrap tmce-active tm_editor_wrap"></div>')
                        .before('<div class="' + editor_tools_class + '">' + editor_tools_html + '</div>')
                        .wrap('<div class="wp-editor-container"></div>');
                    $('.tm_editor_wrap').find('.wp-switch-editor').each(function(n, s) {
                        if ($(s).attr('id')) {
                            var aid = $(s).attr('id'),
                                l = aid.length,
                                mode = aid.substr(l - 4);
                            $(s).attr('id', id + '-' + mode);
                        }
                    });
                    tinymce.init(tmc_settings);
                    if (QTags && quicktags) {
                        quicktags(tqt_settings);
                        QTags._buttonsInit();
                    }
                    $(textarea).closest('.tm_editor_wrap').find('a.insert-media').data('editor', id).attr('data-editor', id);
                }
            });
        },

        removeTinyMCE: function(element) {
            if (!$(element)) {
                return;
            }
            var id, _check;
            $(element).find('textarea').each(function(i, textarea) {
                id = $(textarea).attr('id');
                if (id &&  tinyMCE && tinyMCE.editors) {
                    if (id in  tinyMCE.editors) {
                        _check = tinyMCE.editors[id].getContent();
                        tinyMCE.editors[id].remove();
                    }
                    $(textarea).closest('.tm_editor_wrap').find('.quicktags-toolbar,.wp-editor-tools').remove();
                    $(textarea).unwrap().unwrap();

                    if (_check == '') {
                        $(textarea).val('');
                    }else{
                        $(textarea).val(_check);
                    }
                }
            });
        },
        
        set_field_title: function(obj) {
            if (!obj){
                obj=$(".bitem");
                obj.each(function(i,el){
                    if ($(el).find(".tm-header-title").length==0){
                        return true;
                    }
                    var original_title=$(el).find(".label").html();
                    $(el).data('original_title',original_title);
                    var id=$(el);
                    $(el).find(".tm-header-title").data("id",id);
                    var title= $(el).find(".tm-header-title").val();
                    if (!(title===undefined || title=='')){
                        $(el).find(".label").html(title+' <small>('+original_title+')<\/small>');
                    }

                });               
            }
            else if ($(obj).is(".bitem")){
                var original_title=$(obj).find(".label").html();
                $(obj).data('original_title',original_title);
            }
            if ($(obj).length==0 || !obj.is(".tm-header-title")){
                return;
            }
            var title= obj.val();
            var el=obj.data("id");
            var original_title=$(el).data("original_title");
            if (title===undefined || title==''){                
                $(el).find(".label").html(original_title);
            }else{
                $(el).find(".label").html(title+' <small>('+original_title+')<\/small>');
            }
        },

        set_hidden: function() {
            $('.builder_wrapper').each(function(i, section) {
                $(this).find(".tm_builder_sections").val($(this).find(".bitem ").length);
                $(this).find(".tm_builder_sections_size").val(function() {
                    var _size = $(section).attr("class").split(' ')
                        .map(function(cls) {
                            if (cls.match(/w\d+/g) !== null) {
                                return cls;
                            }
                        })
                        .filter(function(v, k, el) {
                            if (v !== null && v !== undefined) {
                                return v;
                            }
                        });
                    return _size[0];
                });
                $(this).find(".div_size").val(function() {
                    var _size = $(this).closest(".bitem").attr("class").split(' ')
                        .map(function(cls) {
                            if (cls.indexOf("w", 0) !== -1) {
                                return cls;
                            }
                        })
                        .filter(function(v, k, el) {
                            if (v !== null && v !== undefined) {
                                return v;
                            }
                        });
                    return _size[0];
                });
            });
        },

        tm_upload: function (e){
            var $this,$use_images;
            if (e){
                $use_images=$(this);                
            }else{                
                $use_images=$("#temp_for_floatbox_insert .use_images");
            }
            $this=$("#temp_for_floatbox_insert");
            var tm_upload = $this.find(".builder_element_wrap").find(".tm_upload_button");
            var tm_upload_image = $this.find(".builder_element_wrap").find(".tm_upload_image");
            
            if ($use_images.val()=="images"){
                tm_upload.show();
                tm_upload_image.show();
            }else{
                tm_upload.hide();
                tm_upload_image.hide();
            }
        },

        tm_url: function (e){
            var $this,$use_url;
            if (e){
                $use_url=$(this);                
            }else{                
                $use_url=$("#temp_for_floatbox_insert .use_url");
            }
            $this=$("#temp_for_floatbox_insert");
            var use_url = $this.find(".builder_element_wrap").find(".tm_cell_url");            
            if ($use_url.val()=="url"){
                use_url.show();
            }else{
                use_url.hide();
            }
        },

        upload: function(e) {
            e.preventDefault();
            if (wp && wp.media) {
                var _this = $(this).prev("input");
                var _this_image = $(this).next(".tm_upload_image").find("img");
                var _this_image_src = $(this).closest(".options_wrap").find(".tm_option_image");
                if ($(this).data('tm_upload_frame')) {
                    $(this).data('tm_upload_frame').open();
                    return;
                }
                var $tm_upload_frame = wp.media({
                    frame: 'select',
                    library: {
                        type: 'image'
                    },
                    multiple: false
                });                
                $tm_upload_frame.on('select', function() {
                    var media_attachment = $tm_upload_frame.state().get('selection').first().toJSON();
                    _this_image.attr('src',media_attachment.url);
                    _this_image_src.val(media_attachment.url);
                });
                $tm_upload_frame.on('open', function() {
                    var selection = $tm_upload_frame.state().get('library').toJSON();
                    $.each(selection, function(i, _el) {
                        if (_el.url == _this.val()) {
                            var attachment = wp.media.attachment(_el.id);
                            $tm_upload_frame.state().get('selection').add(attachment ? [attachment] : []);
                        }
                    });
                });
                $(this).data('tm_upload_frame',$tm_upload_frame);
                $tm_upload_frame.open();
            } else {
                return false;
            }
        }        
    }

    var _tm_ajax_check=0;

    function tm_license_check(action) {
        if (_tm_ajax_check == 0) {
            _tm_ajax_check=0
            $('.tm-license-button').block({
                message: null,
                overlayCSS: {
                    background: '#fff url(' + tm_epo_admin.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
                    opacity: 0.6
                }
            });
            var data = {
                action: 'tm_'+action+'_license',
                username: $('#tm_epo_envato_username').val(),
                key: $('#tm_epo_envato_purchasecode').val(),
                api_key: $('#tm_epo_envato_apikey').val(),
                security: tm_epo_admin.settings_nonce
            };
            $.post(tm_epo_admin.ajax_url, data, function(response) {
                var html;
                if (!response || response==-1){
                    html=tm_epo_admin.invalid_request;
                }else if(response && response.message && response.result 
                    && (response.result=='-3' || response.result=='-2' 
                        || response.result=='wp_error' || response.result=='server_error') ){
                    html=response.message;
                }else if(response && response.message && response.result && (response.result=='4') ){                        
                    html=response.message;
                }else{
                    html='';
                }
                $('.tm-license-result').html(html);
                $('.tm-license-button').unblock();
            },'json')
            .always(function(response) {
                 $('.tm-license-button').unblock();
                 _tm_ajax_check = 0;
                if (response && response.result && (response.result=='4')){
                    if (action=='activate'){
                        $('.tm-deactivate-license').removeClass('tm-hidden');
                        $('.tm-activate-license').removeClass('tm-hidden').addClass('tm-hidden');
                    }
                    if (action=='deactivate'){
                        $('.tm-deactivate-license').removeClass('tm-hidden').addClass('tm-hidden');
                        $('.tm-activate-license').removeClass('tm-hidden');
                    }
                }
            });
        }             
    }

    function tm_display_settings(select){
        var val=select.val();
        var row1=$('#tm_epo_options_placement').closest('tr');
        var row2=$('#tm_epo_totals_box_placement').closest('tr');
        var row3=$('#tm_epo_options_placement_custom_hook').closest('tr');
        var row4=$('#tm_epo_totals_box_placement_custom_hook').closest('tr');
        if (val=="action"){
            row1.hide();
            row2.hide();
            row3.hide();
            row4.hide();
        }else{
            row1.show();
            row2.show();
            tm_options_placement_settings($('#tm_epo_options_placement'));
            tm_totals_box_placement_settings($('#tm_epo_totals_box_placement'));
        }
    }

    function tm_options_placement_settings(select){
        var val=select.val();
        var row1=$('#tm_epo_options_placement_custom_hook').closest('tr');
        if (val=="custom"){
            row1.show();
        }else{
            row1.hide();
        }
    }

    function tm_totals_box_placement_settings(select){
        var val=select.val();
        var row1=$('#tm_epo_totals_box_placement_custom_hook').closest('tr');
        if (val=="custom"){
            row1.show();
        }else{
            row1.hide();
        }
    }
    function tm_css_styles_settings(select){
        var val=select.val();
        var row1=$('#tm_epo_css_styles_style').closest('tr');
        if (val=="on"){
            row1.show();
        }else{
            row1.hide();
        }
    }

    $(document).ready(function() {
        $.tmEPOAdmin.initialitize();
        if ($('.tm-settings-wrap').length>0){            
            $('.tm-activate-license').on('click', function(e) {
                e.preventDefault();
                tm_license_check('activate');
            });
            $('.tm-deactivate-license').on('click', function(e) {
                e.preventDefault();
                tm_license_check('deactivate');
            });

            $('#tm_epo_display').on('change', function(e) {
                tm_display_settings($(this));
            });
            $('#tm_epo_options_placement').on('change', function(e) {
                tm_options_placement_settings($(this));
            });
            $('#tm_epo_totals_box_placement').on('change', function(e) {
                tm_totals_box_placement_settings($(this));
            });
            $('#tm_epo_css_styles').on('change', function(e) {
                tm_css_styles_settings($(this));
            });
            tm_display_settings($('#tm_epo_display'));
            tm_options_placement_settings($('#tm_epo_options_placement'));
            tm_totals_box_placement_settings($('#tm_epo_totals_box_placement'));
            tm_css_styles_settings($('#tm_epo_css_styles'));
        }
        $.tm_tooltip();
    });
})(jQuery);
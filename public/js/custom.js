$(document).ready(function () {

    var siteRoot = $('body').data('siteRoot');
    /*
     * Code below, saves user my-account details before request leave us to braintree
     * It just checks whether payment form has address fields and if form was not
     * pre filled earlier
     */
    var $braintree_billing_details = $('.braintree-billing-details'),
        $prefilled_data = $braintree_billing_details.find('#has-prefilled-data');

    if($braintree_billing_details.length) {
        $braintree_billing_details.parents('form:first').submit(function() {
            var $submit = $(this).find(':submit'),
                $exp_date_month = $('#exp-date-month'),
                $exp_date_year = $('#exp-date-year'),
                $braintree_exp_date = $('#braintree_credit_card_exp');

            /* form already submitted */
            if($submit.hasClass('disabled')) {
                return false;
            }

            $process_form = true;
            if(!$exp_date_month.val().length) {
                $exp_date_month.focus();
                $process_form = false;
            }
            if(!$exp_date_year.val().length) {
                /* let month be focused first on error */
                if($process_form) {
                    $exp_date_year.focus();
                }
                $process_form = false;
            }

            /* stop submitting if cc exp date is wrong */
            if(!$process_form) {
                $exp_date_month.parents('.control-group:first').addClass('error');
                return false;
            } else {
                $braintree_exp_date.val($exp_date_month.val() + '/' + $exp_date_year.val());
            }

            $submit.addClass('disabled');

            // do not save address to my-account if form was prefilled using data from my-account
            if(!$prefilled_data.length) {
                $.ajax({
                    url: siteRoot + '/my-account/edit-account',
                    type: 'POST',
                    async: false,
                    data: {
                        firstname: $('#customer_first_name').val(),
                        lastname: $('#customer_last_name').val(),
                        street: $('#billing_street_address').val(),
                        postal_code: $('#billing_postal_code').val(),
                        state: $('#billing_region').val(),
                        city: $('#billing_locality').val(),
                        country: $('#billing_country_name').val(),
                    }
                });
            }
        });
    }

    /*
     * Admin User deletion modal
     */
    var $user_deletion_modal = $('#user-deletion'),
        $user_deletion_buttons = $('.user-remove');
    if($user_deletion_buttons.length) {
        $user_deletion_buttons.click(function() {
            var $this = $(this);
            $user_deletion_modal.find('.modal-body b').text(
                $this.parents('tr:first').find('td:eq(0)').text()
            );
            $user_deletion_modal.find('form :input[name=id]').val($this.data('user-id'));

        })
    }

    // configure tooltip messages in place of default browser title popovers
    $("a[rel=tooltip]").tooltip({
        placement: 'bottom'
    });
    $(".subscribe-now, .btn.disabled.change-plan").tooltip({});
    $("button.request-deployment[rel=tooltip]").tooltip({
        placement: 'left'
    });
    $('.tooltip-top').tooltip({
        placement: 'top'
    });
    
    /* prevent click event and init popover */
    $('[rel=popover]').click(function(){return false;}).popover();
    $('[rel=popover]').mouseover(function(){
        $(this).find('i.icon').removeClass('icon-blue').addClass('icon-white');
    });
    $('[rel=popover]').mouseout(function(){
        $(this).find('i.icon').removeClass('icon-white').addClass('icon-blue');
    });
    
    
    var $deployment_modal = $('#store-deployment'),
        $deployment_form = $deployment_modal.find('form'),
        $rollback_name = $deployment_modal.find('.rollback-name'),
        $commit_comment = $deployment_modal.find('#commit_comment'),
        $deploy_table_body = $deployment_modal.find('.table tbody');

    $deploy_table_body.on('click', '.request-deployment.request-buy', function(e) {
        if ($(this).is('.disabled')){
            return false;
        }
        e.stopPropagation();
        e.preventDefault();
        var $this = $(this);
        form_string = '<form id="buy_request" method="post" action="' + siteRoot + '/braintree/payment">';
        form_string += '<input type="hidden" name="domain" value="'+$this.data('store-domain')+'" />';
        form_string += '<input type="hidden" name="source" value="deployment-request" />';
        form_string += '<input type="hidden" name="pay-for" value="extension" /><input type="hidden" name="id" value="'+$this.val()+'" /></form>';
        $('body').append(form_string);
        $('#buy_request').submit();
    });
    // deployment modals
    $('.panel.deployment .btn').click(function() {
        var $this = $(this);
        if(!$this.hasClass('disabled')) {
            var $domain = $this.nextAll('.storedomain').val(),
                $form_action = siteRoot + '/queue/[replace]/domain/' + $domain,
                $remove_class = '',
                $add_class = '';
            if($this.hasClass('rollback-button')) {
                // set name of rollback ( extension name | manual commit | commit comment )
                var $rollback_name_string = ' <span class="label label-info">',
                    $comment = $this.data('comment'),
                    $match = $comment.match(/Adding ([^\(]*[^\(\s])(?: \(.*\))*/i);
                if($match) {
                    $rollback_name_string += $.trim($match[1])+'</span> installation';
                } else if($comment) {
                    $rollback_name_string += $comment+'</span>';
                } else {
                    $rollback_name_string += 'Manual Commit</span>';
                }
                $rollback_name.empty().append($rollback_name_string);
                // set form action path
                $form_action = $form_action.replace('[replace]', 'rollback');
                // show modal with proper pre-class
                $remove_class = 'modal-commit modal-deploy';
                $add_class = 'modal-rollback';
            } else if($this.hasClass('commit-button')) {
                // set form action path
                $form_action = $form_action.replace('[replace]', 'commit');
                // reset comment
                $commit_comment.val('');
                // show modal with proper pre-class
                $remove_class = 'modal-rollback modal-deploy';
                $add_class = 'modal-commit';
            } else if($this.hasClass('deploy-button')) {
                var $actual_form_action = $form_action.replace('[replace]', 'request-deployment');
                if($deployment_form.attr('action') != $actual_form_action) {
                    $deploy_table_body.empty();
                    $.ajax({
                       url : $form_action.replace('[replace]', 'fetch-deployment-list'),
                       async : false,
                       success : function(html) {
                           if(typeof html == 'string' && html.length) {
                               $deploy_table_body.append(html.replace(/(Adding )([^\(]*[^\(\s])( \(.*\))*\</ig, '$2<'));
                           }
                           $(".btn.request-deployment.disabled").tooltip({placement: 'left'});
                       }
                    });
                }
                // set form action path
                $form_action = $actual_form_action;
                // show modal with proper pre-class
                $remove_class = 'modal-rollback modal-commit';
                $add_class = 'modal-deploy';
            }
            $deployment_modal.removeClass($remove_class).addClass($add_class).modal('show');
            $deployment_form.attr('action', $form_action);
        }
        return false;
    });

    var $modal_close_store = $('#close-store'),
        $modal_close_store_form = $modal_close_store.find('form');
    $modal_close_store.find('form .btn-danger').click(function() {
        var $this = $(this);
        // do not allow for multiple clicks
        if(!$this.hasClass('disabled')) {
            $this.addClass('disabled');
            $modal_close_store_form.submit();
        }
        return false;
    });

    /* DELETE STORE BUTTON - prevent accordion click event */
    $('.delete-store').click(function(event){
        event.stopPropagation();
        var $this = $(this),
            $store_name,
            $modal_store_name_container = $modal_close_store.find('.close-store-name');
        if($this.hasClass('admin')) {
            $store_name = $this.parent().parent().children('td:eq(1)').text();
        } else {
            $store_name = $this.parent().nextAll('.title').text();
        }
        $modal_close_store_form.attr('action', $this.attr('href'));
        if($store_name.length) {
            $modal_store_name_container.text(' "'+$store_name+'"');
        } else {
            $modal_store_name_container.text('');
        }
        $modal_close_store.modal('show');
        return false;
    });
    
    var $extension_button = $('.new-store-extension-installer'),
        $extension_id = $('#new-store-extension-id');
    $extension_button.click(function(e) {
        e.preventDefault();
        $extension_id.val($(this).data('extension-id')).parent('form').submit();
    });

    var $view_store = $('.view-store'),
        $admin_panel = $('.admin-panel');

    if($view_store.length) {
        $view_store.click(function(e) {
            // stop bootstrap collapsing
            e.stopPropagation();
        })
    }

    if($admin_panel.length) { 
        $admin_panel.click(function(e) { 
        // stop bootstrap collapsing 
        e.stopPropagation();
        })
    }

    /* STORE EXTENSIONS ISOTOPE */
    var $extensions_isotope = $('.extensions_well > #container'),  // it should be change to #extensions_isotope or smth
        $extensions_filter_container = $('#options'),
        $extensions_filter_options = $extensions_filter_container.find('li a'),
        $extensions_filters_search = '',
        $extensions_filters = '',
        ElementPad        = 5,
        ElementWidth      = 135 + (ElementPad * 2),
        ElementHeight     = 112,
        ColumnWidth       = ElementWidth + ElementPad,
        RowHeight         = ElementHeight + ElementPad;

    if($extensions_isotope.length) {
        $extensions_isotope.imagesLoaded(function() {
            $('.element.premium .wrapper div.icon').css({
                'margin-top': function(){
                    var margin = (112 - $(this).find('img').height()) / 2;
                    if(margin < 0){ margin = 0; }
                    return margin;
                }
            });
        })
        
        // Pre-toggle "All" buttons
        $('li a.btn-all').parent().addClass('active');
        //$('.btn-all').button('toggle');
        
        $extensions_isotope.isotope({
            masonry : {
                columnWidth : ColumnWidth
              },
              masonryHorizontal : {
                rowHeight: RowHeight
              },
              cellsByRow : {
                columnWidth : ColumnWidth * 2,
                rowHeight : RowHeight * 2
              },
              cellsByColumn : {
                columnWidth : ColumnWidth * 2,
                rowHeight : RowHeight * 2
              }
        });
        $extensions_filter_options.click(function(e) {
            var $this = $(this);
            var $dropdown = $this.parent().parent();
            var $group = $dropdown.parent();
            var selector = 'selected';
            
            if(! $this.hasClass(selector)) {
            	$dropdown.find('li').removeClass('active');
	    		$dropdown.find('li a').removeClass(selector);
						
            	$this.addClass(selector).parent().addClass('active');
            	$title = $group.attr('data-title');
            	$group.find('a.btn.dropdown-toggle').html($title + ": " + $this.html() + ' <span class="caret"></span>');
            	
            	$extensions_filters = '';
            	$extensions_filter_container.find('.' + selector).each(function() {
                    var $option = $(this).data('option-value');
                    if($option != '*') {
                        $extensions_filters += $option;
                    }
                });
                $extensions_isotope.isotope({filter: $extensions_filters + $extensions_filters_search});
            }
            
            // Bootstrap + isotope conflict fix
            $('.btn-group.open').removeClass('open');
            
            /*
             * Original filters code
             * 
             *if(! $this.hasClass(selector)) {
                $this.siblings()
                     .removeClass(selector)
                     .removeClass('active')
                     .end()
                     .addClass(selector + ' active');
                var $filter = '';
                $extensions_filter_container.find('.' + selector).each(function() {
                    var $option = $(this).data('option-value');
                    if($option != '*') {
                        $filter += $option;
                    }
                });
                $extensions_isotope.isotope({filter: $filter});
            }*/
            
            return false;
        });
        
        
        // Filter as you type functions
        var keyTime, // it informs keyup event when last key was pressed
            delayTime = 500, // pause between key pressing before we fire up filtering - default = 1000 ms = 1 second
            $search_input = $('.search-as-you-type-input'),
            lastValue_search_input = '',
            lastTimeout,
            $extensions = $('.element');

        var f_filterExtensionsByQuery = function f_filterExtensionsByQuery() {
            var queryString = $search_input.val().toLowerCase();
            $extensions.each(function(k, e) {
                var $element = $(e);
                if(!queryString ||
                   (
                     $element.find('.info').text().toLowerCase().match(queryString) ||
                     $element.find('.description').text().toLowerCase().match(queryString)
                   )
                ) {
                    $element.addClass('matches');
                    $extensions_filters_search = '.matches';
                } else {
                    $element.removeClass('matches');
                    $extensions_filters_search = '';
                }
            });

            $extensions_isotope.isotope({ filter: $extensions_filters + '.matches' });
        }

        // prevent form submitting for query search field
        $search_input.parents('form:first').submit(function() { return false; })
        $search_input.keyup(function(e) {
            var newValue_search_input = $search_input.val();
            // allow filtering only when query input was filed or truncated
            if(lastValue_search_input.length != newValue_search_input.length || lastValue_search_input != newValue_search_input) {
                // set lastValue to current value
                lastValue_search_input = newValue_search_input;
                keyTime = (new Date()).getTime(); // pressed key ms
                // erase last timeout
                if(lastTimeout) {
                    clearTimeout(lastTimeout);
                }
                // set new timeout execution
                lastTimeout = setTimeout(f_filterExtensionsByQuery, delayTime);
            }
        });
    }

    // EVENT: On click "Install" button
    $('.install').click(function(event){
        "use strict";
        var $this = $(this);

        $this.addClass('disabled');
        $.ajax({
            url     : $extensions_filter_container.data('form-action'),
            type    : 'POST',
            data    : {extension_id : $this.data('install-extension')},
            success : function(response) {
                if(response != 'error' && response != '') {
                    var $replacement = $('<span class="label update-status label-info pull-right">Pending</span>');
                    $this.replaceWith($replacement);
                    $replacement.parents('.element:first').data('store-extension-id', response);
                    f_update_status($replacement);
                }
            }
        });
        
        
        return false;
    });
    
    var _screenshotCarousel = $('#screenshotCarousel');
    var _screenshotModal = $('#screenshotModal').modal({show: false});
    
    // EVENT: On click homepage thumbnail
    $('a.index-thumbnail').click(function(event){
        var _this = $(this);
        var _thumbs = _this.parent().parent();
        var _carousel = $('#screenshotCarousel div.carousel-inner');
        
        _carousel.empty();
        
        var active = true;
        
        _thumbs.find('li').each(function(index){
            var _screenshot = $(this);

            var _item = $('<div>').addClass('item');
            
            if(active){
                _item.addClass('active');
                active = false;
            }
            
            _item.append($('<div>')
                .addClass('modal-header')
                .append($('<button>')
                    .addClass('close')
                    .attr('type', 'button')
                    .attr('data-dismiss', 'modal')
                    .html('&times;')
                )
                .append($('<h5>')
                    .text(_screenshot.find('a').attr('data-title'))
                )
            ).append($('<div>')
                .addClass('modal-body')
                .css('max-height','100%')
                .append($('<img>')
                    .attr('src', _screenshot.find('a').attr('href'))
                )
            );
            _carousel.append(_item);
        });
        
        _screenshotModal.modal('show');
        _screenshotCarousel.carousel({'interval': false});
        $('.carousel-control').css('top', '56%');
        
        event.preventDefault();
        event.stopPropagation();
    });
    
    // EVENT: On click extension's "View screens" button
    $('a.btn-screenshots').click(function(event){
        "use strict";
        
        var _this = $(this);
        var _extension = _this.parent().parent().parent();
        var _carousel = $('#screenshotCarousel div.carousel-inner');
        
        _carousel.empty();
        
        var active = true;
        
        _extension.find('.screenshots li').each(function(){
            var _screenshot = $(this);
            
            var _item = $('<div>').addClass('item');
            if(active){
                _item.addClass('active');
                active = false;
            }
            _item.append($('<div>')
                .addClass('modal-header')
                .append($('<button>')
                    .addClass('close')
                    .attr('type', 'button')
                    .attr('data-dismiss', 'modal')
                    .html('&times;')
                )
                .append($('<h5>')
                    .text(_screenshot.attr('data-id'))
                )
            ).append($('<div>')
                .addClass('modal-body')
                .css('max-height','100%')
                .append($('<img>')
                    // Preload function
                    /*.load(function(){
                    })*/
                    .attr('src', _screenshot.text())
                )
            );
            _carousel.append(_item);
        });
        
        _screenshotModal.modal('show');
        _screenshotCarousel.carousel({'interval': false});
        
        // Code for resizing and centering modal
        /*_screenshotCarousel.bind('slid', function() {
            _screenshotModal.css({
                width: 'auto',
                'margin-left': function(){
                    return -($(this).width() / 2);
                }
            });
        });*/
        
        $('.carousel-control').css('top', '56%');
        
        event.preventDefault();
        event.stopPropagation();
    });
    
    // change size of clicked element
    $extensions_isotope.find('.element').click(function() {
        if( !($(this).hasClass('large'))){
            $('.large').removeClass('large').find('div.extras').addClass('hidden');
        }
        $(this).toggleClass('large').find('div.extras').toggleClass('hidden');
        $extensions_isotope.isotope('reLayout');
    });
    /* STORE EXTENSIONS ISOTOPE */

    /* payment form - display dropdown for US and text field for all other countries */
    changeInputSelect();    
        
    $('.form-stacked.form-input-select select.select-country').change(function() {
        changeInputSelect();
    });

    $('.form-stacked.form-input-select button').click(function() {
        if($('.form-stacked.form-input-select input.input-state').css('display') == 'none') {
            $('.form-stacked.form-input-select input.input-state').remove();
        }

        if($('.form-stacked.form-input-select select.select-state').css('display') == 'none') {
            $('.form-stacked.form-input-select select.select-state').remove();
        }
    });
    
    
    $('form#extension-filter-form select').change(function() {
        current = window.location.href;
        isIndex = window.location.href.match(/index/g);
      
        if(isIndex == null) {
             current = window.location.href.replace('/extension', '/extension/index');
        }
        
        current = current.replace('/edition/CE', '').replace('/edition/EE', '').replace('/edition/PE', '').replace('/edition/ALL', '');
        window.location.href = current + '/edition/' + $(this).val();
    });
    
    $('#extension-form select#edition').change(function() {
        changeVersionByEdition();
    });
    
    changeVersionByEdition();
    
    $('#extension-form').submit(function() {
        $('#extension-form select[name$="_version"]:hidden').remove();
    });
    
    $("#iconmenu-help, #context-help i").click(function () {
        var $helpBlock = $('#wrap-context-help');
        $helpBlock.animate({
            right: parseInt($helpBlock.css('right'), 10) == 0 ?
                -$helpBlock.outerWidth() :
                0
        });
    });

    /* Store extensions status updater */
    var f_update_status = function($element) {
        setTimeout(function() {
            $.ajax({
                url : siteRoot + '/queue/getstatus',
                type : 'POST',
                dataType : 'json',
                data : { extension_id : $element.parents('.element:first').data('store-extension-id') },
                success: function(response) {
                    var $replacement = [];
                    if(response == 'processing') {
                        $replacement = $('<span class="label update-status label-important pull-right">Installing</span>');
                        f_update_status($replacement);
                    } else if(response == 'ready') {
                        $replacement = $('<span class="label update-status label-success pull-right">Success</span>');
                        setTimeout(function() { location.reload(); }, 500);
                    } else {
                        f_update_status($element);
                    }

                    if($replacement.length) {
                        $element.replaceWith($replacement);
                    }
                }
            });
        }, 5000);
    };
    $status_labels = $('.update-status');
    if($status_labels.length) {
        $status_labels.each(function(k, e) {
            $element = $(e);
            $element.parents('.element').addClass('large').find('.extras').removeClass('hidden');
            f_update_status($element);
        });
        $extensions_isotope.isotope('reLayout');
    }
});

function changeInputSelect() {
    if( $('.form-stacked.form-input-select select.select-country').val() == 'United States' ) {
        $('.form-stacked.form-input-select select.select-state').show();
        $('.form-stacked.form-input-select input.input-state').hide();
    } else {
        $('.form-stacked.form-input-select select.select-state').hide();
        $('.form-stacked.form-input-select input.input-state').show();
    }
}

function changeVersionByEdition() {
    val = $('#extension-form select#edition').val();

    $('#extension-form select#from_version').hide();
    $('#extension-form select#to_version').hide();
    $('#extension-form select.'+val).show();
}

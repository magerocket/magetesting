$(document).ready(function () {
    // configure tooltip messages in place of default browser title popovers
    $("a[rel=tooltip]").tooltip({
        placement: 'bottom'
    });

    /* prevent click event and init popover */
    $('[rel=popover]').click(function(){return false;}).popover();

    var $deployment_modal = $('#instance-deployment'),
        $deployment_form = $deployment_modal.find('form'),
        $rollback_name = $deployment_modal.find('.rollback-name'),
        $commit_comment = $deployment_modal.find('#commit_comment'),
        $deploy_table_body = $deployment_modal.find('.table tbody'),
        $base_url = $('#base-url').val();
    // deployment modals
    $('.panel.deployment .btn').click(function() {
        var $this = $(this);
        if(!$this.hasClass('disabled')) {
            var $domain = $this.nextAll('.instancedomain').val(),
                $form_action = $base_url+'/queue/[replace]/domain/'+$domain,
                $remove_class = '',
                $add_class = '';
            if($this.hasClass('rollback-button')) {
                // set name of rollback ( extension name | manual commit | commit comment )
                var $rollback_name_string = ' <span class="label label-warning">',
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
                // set form action path
                $form_action = $form_action.replace('[replace]', 'deploy');
                if($deployment_form.attr('action') != $form_action) {
                    $deploy_table_body.empty();
                    $.ajax({
                       url : $base_url+'/queue/fetch-deployment-list/domain/'+$domain,
                       async : false,
                       success : function(html) {
                           if(typeof html == 'string' && html.length) {
                               $deploy_table_body.append(html.replace(/(Adding )([^\(]*[^\(\s])( \(.*\))*\</ig, '$2<'));
                           }
                       }
                    });
                }
                // show modal with proper pre-class
                $remove_class = 'modal-rollback modal-commit';
                $add_class = 'modal-deploy';
            }
            $deployment_modal.removeClass($remove_class).addClass($add_class).modal('show');
            $deployment_form.attr('action', $form_action);
        }
        return false;
    });

    var $modal_close_instance = $('#close-instance'),
        $modal_close_instance_form = $modal_close_instance.find('form');
    $modal_close_instance.find('form .btn-danger').click(function() {
        var $this = $(this);
        // do not allow for multiple clicks
        if(!$this.hasClass('disabled')) {
            $this.addClass('disabled');
            $modal_close_instance_form.submit();
        }
        return false;
    });

    /* DELETE STORE BUTTON - prevent accordion click event */
    $('.delete-store').click(function(event){
        event.stopPropagation();
        var $this = $(this),
            $instance_name = $this.parent().nextAll('.title').text(),
            $modal_instance_name_container = $modal_close_instance.find('.close-instance-name');
        $modal_close_instance_form.attr('action', $this.attr('href'));
        if($instance_name.length) {
            $modal_instance_name_container.text(' "'+$instance_name+'"');
        } else {
            $modal_instance_name_container.text('');
        }
        $modal_close_instance.modal('show');
        return false;
    });
    
    var $extension_button = $('.new-instance-extension-installer'),
        $extension_id = $('#new-instance-extension-id');
    $extension_button.click(function(e) {
        e.preventDefault();
        $extension_id.val($(this).data('extension-id')).parent('form').submit();
    });

    var $admin_extension = $('table.admin-extensions'),
        $admin_extension_uploader = $('#fileupload'),
        $screenshots = $('.screenshots'),
        $logo_container = $('.logo-container');

    // allow lightbox for admin extension screenshots
    if($admin_extension.length) {
        $admin_extension.find('.btn.show-screenshots').click(function() {
            $(this).next('.screenshots-container').children('a:first').click();
            return false;
        });
    }

    if($admin_extension_uploader.length) {
        $admin_extension_uploader.on('click', '.btn.as-logo', function() {
            var $checkbox = $(this).children('input');
            $checkbox.attr('checked', !$checkbox.attr('checked'));
            $admin_extension_uploader.find('.btn.as-logo > input').not($checkbox).attr('checked', false);
        });
        /* handle images uploading */
        var $directory_hash = $('#directory_hash').val();
        $directory_hash = $directory_hash ? $directory_hash : '';
        // Initialize the jQuery File Upload widget:
        $admin_extension_uploader.fileupload({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $admin_extension_uploader.attr('action'),
            dropZone: $('.fileinput-button.btn.btn-success'),
            dataType: 'json',
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            formData: {directory_hash: $directory_hash}
        }).bind('fileuploadsubmit',
            function(e, data){
                data.formData = {
                    checked: (!data.context.find('input:checkbox').attr('checked') ? 0 : 1),
                    directory_hash: $directory_hash
                };
            }
        ).bind('fileuploadcompleted',
            function (e, data) {
                e.preventDefault();
                $.each(data.result, function(i, file) {
                    if(typeof file.error == "undefined") {
                        if(file.as_logo == 1) {
                            $logo_container.children().remove();
                            $logo_container.append(
                                $('<input type="hidden" name="logo" value="'+file.name+'" />')
                                .add($('<img src="'+file.url+'" />'))
                            );
                        } else {
                            $screenshots.append(
                                $('<input type="hidden" name="screenshots_ids[]" value="" />')
                                .add($('<input type="hidden" name="screenshots[]" value="'+file.name+'" />'))
                                .add($('<img src="'+file.url+'" />'))
                            );
                        }

                        setTimeout(function() {
                            data.context.fadeOut(500, function(){ $(this).remove(); });
                        }, 500);
                    }
                });
            }
        );
    }

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

            // create fake link to avoid popup closing
            $('body').append('<a class="hidden" id="open_admin_panel">Fake click</a>');
            var $this = $(this),
                $created_link = $('#open_admin_panel');
            $created_link.click(function(){
                var $opened_window = window.open($this.prev().attr('href')+'/'+$this.data('backend-name')),
                    $attempts = 0,
                    $fill_window_form = function() {
                    var $window_context = $($opened_window.document).contents();
                    var $login = $window_context.find('input:text'),
                        $password = $login.end().find('input:password');
                    if($login.length) {
                        $login.val($this.data('admin-login'))
                        .end().find('input:password').val($this.data('admin-password'))
                        .parents('form:first').submit();
                        $opened_window.focus();
                    } else if(!$window_context.find('body > *').length && $attempts < 100) {
                        $attempts++;
                        setTimeout($fill_window_form, 100);
                    }
                }
                setTimeout($fill_window_form, 100);
                $created_link.remove();
                return false;
            });
            $created_link.click();
        });
    }

    /* INSTANCE EXTENSIONS ISOTOPE */
    var $extensions_isotope = $('.extensions_well > #container'),
        $extensions_filter_container = $('#options'),
        $extensions_filter_options = $extensions_filter_container.find('button')
        ElementPad        = 5,
        ElementWidth    = 135 + (ElementPad * 2),
        ElementHeight    = 112,
        ColumnWidth        = ElementWidth + ElementPad,
        RowHeight        = ElementHeight + ElementPad;

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
		$('.btn-all').button('toggle');
		
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
        $extensions_filter_options.click(function() {
            var $this = $(this);
            var selector = 'selected';
            if(! $this.hasClass(selector)) {
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
            }
            return false;
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
                $this.addClass('hidden').prev('.progress').removeClass('hidden');
            }
        });
        
        
        return false;
    });
    
    var _screenshotCarousel = $('#screenshotCarousel');
    var _screenshotModal = $('#screenshotModal').modal({show: false});
    
    // EVENT: On click "View screens" button
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
        $(this).toggleClass('large').find('div.extras').toggleClass('hidden');
        $extensions_isotope.isotope('reLayout');
    });
    /* INSTANCE EXTENSIONS ISOTOPE */
});

$(document).ready(function () {
    // configure tooltip messages in place of default browser title popovers
    $("a[rel=tooltip]").tooltip({
        placement: 'bottom'
    });

    /* prevent click event and init popover */
    $('[rel=popover]').click(function(){return false;}).popover();

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
});
jQuery(document).ready(function($) {
    $('#bppc-form').submit(function(e) {
        e.preventDefault();
        var pageTitles = $('#page-input').val().split(',');
        var postType = $('#post-type').val();
        var parentId = $('#parent').val();
        var loader = $('#bppc-loader');
        var message = $('#bppc-message');
        var count = 0;
        var total = pageTitles.length;
        message.text('');
        loader.show();
        pageTitles.forEach(function(title) {
            $.ajax({
                url: bppc_vars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bppc_create_page',
                    title: title.trim(),
                    post_type: postType,
                    parent_id: parentId
                },
                success: function(data) {
                    count++;
                    var percent = Math.round((count / total) * 100);
                    loader.text(percent + '%');
                    if (count == total) {
                        loader.hide();
                        message.text('Pages are created');
                    }
                }
            });
        });
    });

    $('#post-type').change(function() {
        var postType = $(this).val();
        $.ajax({
            url: bppc_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'bppc_get_pages',
                post_type: postType
            },
            success: function(data) {
                $('#parent').html(data);
            }
        });
    });
});

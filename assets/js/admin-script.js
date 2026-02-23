jQuery(document).ready(function($) {
    const $formWrapper = $('#can-note-form-wrapper');
    const $form = $('#can-note-form');
    const $list = $('#can-notes-list');
    const $toggleBtn = $('#can-toggle-form');
    const $searchInput = $('#can-search-input');

    // Initial Load
    fetchNotes();

    // Toggle Form
    $toggleBtn.on('click', function() {
        resetForm();
        $formWrapper.slideToggle();
        $(this).toggleClass('active');
    });

    $('#can-cancel-form').on('click', function() {
        $formWrapper.slideUp();
        $toggleBtn.removeClass('active');
        resetForm();
    });

    // Save Note
    $form.on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#can-save-note');
        $btn.addClass('is-loading').prop('disabled', true);

        const formData = {
            action: 'can_save_note',
            nonce: canData.nonce,
            id: $('#can-note-id').val(),
            title: $('#can-note-title').val(),
            content: $('#can-note-content').val(),
            color_label: $('input[name="color_label"]:checked').val()
        };

        $.post(canData.ajax_url, formData, function(response) {
            $btn.removeClass('is-loading').prop('disabled', false);
            if (response.success) {
                $formWrapper.slideUp();
                $toggleBtn.removeClass('active');
                resetForm();
                fetchNotes();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Edit Note
    $list.on('click', '.can-edit-note', function() {
        const $item = $(this).closest('.can-note-item');
        const id = $item.data('id');
        const title = $item.find('.can-note-title').text();
        const content = $item.find('.can-note-body').text().trim();
        const color = $item.attr('class').split('label-')[1].split(' ')[0];

        $('#can-note-id').val(id);
        $('#can-note-title').val(title);
        $('#can-note-content').val(content);
        $(`input[name="color_label"][value="${color}"]`).prop('checked', true);

        $formWrapper.slideDown();
        $toggleBtn.addClass('active');
        $('html, body').animate({ scrollTop: $formWrapper.offset().top - 100 }, 500);
    });

    // Delete Note
    $list.on('click', '.can-delete-note', function() {
        if (!confirm(canData.confirm_delete)) return;

        const id = $(this).closest('.can-note-item').data('id');
        
        $.post(canData.ajax_url, {
            action: 'can_delete_note',
            nonce: canData.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                fetchNotes();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Search Functionality (Debounced)
    let searchTimer;
    $searchInput.on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            fetchNotes($(this).val());
        }, 300);
    });

    function fetchNotes(search = '') {
        $list.html('<div class="can-loading"><span class="spinner is-active"></span> Loading...</div>');
        
        $.get(canData.ajax_url, {
            action: 'can_fetch_notes',
            nonce: canData.nonce,
            search: search
        }, function(response) {
            if (response.success) {
                $list.html(response.data.html);
            }
        });
    }

    function resetForm() {
        $form[0].reset();
        $('#can-note-id').val('');
    }
});

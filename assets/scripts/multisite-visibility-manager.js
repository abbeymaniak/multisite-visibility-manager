jQuery(document).ready(function ($) {
    function updateProgress(current, total)
    {
        let percent = Math.round((current / total) * 100);
        $('.progress-fill').css('width', percent + '%');
        $('.progress-text').text(percent + '% Complete');
    }

    // Single site selection toggle
    $('.visibility-toggle').on('change', function () {
        const checkbox = $(this);
        const siteID = checkbox.data('site');
        const status = checkbox.is(':checked');

        checkbox.prop('disabled', true);

        $.post(MVM_AJAX.ajax_url, {
            action: 'mvm_update_visibility',
            site_id: siteID,
            status: status,
            _ajax_nonce: MVM_AJAX.nonce
        }, function (response) {
            checkbox.prop('disabled', false);
            if (response.success) {
                checkbox.closest('tr').find('.status').text(status ? 'Discouraged' : 'Allowed');
                alert(response.data.message);
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Select / Deselect bulk sites
    $('#select-all').on('change', function () {
        $('.site-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Bulk update with progress indicator.
    $('#apply-bulk').on('click', function (e) {
        e.preventDefault();
        const action = $('#bulk-action').val();

		console.log("this is the action", action);
        if (!action) {
            alert('Please select a bulk action');
            return;
        }

        const siteIDs = $('.site-checkbox:checked').map(function () {
            return $(this).data('site');
        }).get();

        console.log('Selected Site IDs for Bulk Update:', siteIDs);

        if (siteIDs.length === 0) {
            alert('Please select at least one site');
            return;
        }

        $('#progress-modal').removeClass('hidden');
        updateProgress(0, siteIDs.length);

        let processed = 0;

        function processNext()
        {
            if (processed >= siteIDs.length) {
                $('#progress-modal').addClass('hidden');
                alert('Bulk update completed successfully!');
                return;
            }

            let siteID = siteIDs[processed];

            console.log(siteID);
            $.post(MVM_AJAX.ajax_url, {
                action: 'mvm_bulk_update_visibility',
                site_id: siteIDs,
                status: action,
                _ajax_nonce: MVM_AJAX.nonce
            }, function (response) {
                if (response.success) {
                    const row = $('.visibility-toggle[data-site="' + siteID + '"]').closest('tr');
                    row.find('.status').text(action === 'discourage' ? 'Discouraged' : 'Allowed');
                    row.find('.visibility-toggle').prop('checked', action === 'discourage');
                }
                processed++;
                updateProgress(processed, siteIDs.length);
                processNext();
            });
        }

        processNext();
    });
});

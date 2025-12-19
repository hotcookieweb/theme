jQuery(function($){
    // remove default WooCommerce ajax handler
    $('.product_cat-build-a-box a.add_to_cart_button')
        .removeClass('ajax_add_to_cart')
        .off('click');

    // intercept single product form submit
    $(document).on('submit', 'form.cart', function(e){
        if ($(this).find('.single_add_to_cart_button').length) {
            e.preventDefault();
            $(this).find('.single_add_to_cart_button').trigger('hc_buildabox');
        }
    });

    // intercept shop page button click
    $(document).on('click', '.product_cat-build-a-box a.add_to_cart_button', function(e){
        e.preventDefault();
        $(this).trigger('hc_buildabox');
    });
    // Globals
    let boxSize = 0;
    let productId = 0;
    let boxDiscount = '';

    $(document).on('hc_buildabox', '.single_add_to_cart_button, .add_to_cart_button', function(){
        const $buildButton = $(this);
        const $product     = $buildButton.closest('.product');

        boxSize     = parseInt($product.find('.box-config').data('box-size'), 10) || 0;
        productId   = parseInt($product.find('[name="add-to-cart"]').val(), 10)
                    || parseInt($product.find('.add_to_cart_button').data('product_id'), 10)
                    || 0;
        boxDiscount = ($product.find('.box-discount').data('box-discount') ?? '').toString().trim();

        if (boxSize <= 0 || productId === 0 || boxDiscount === '') {
            alert('Product config error: missing box size, product ID, or discount');
            return;
        }

        // disable the button while modal is active
        $buildButton.prop('disabled', true).addClass('disabled');

        $.ajax({
            url: hc_box_modal_params.ajax_url,
            data: { action: 'hc_get_box_products' },
            type: 'GET',
            dataType: 'json',
            success: function(response){
                if (response.success) {
                    $(document.body).WCBackboneModal({
                        template: 'hc-modal-add-box-products',
                        variable: response.data
                    });
                }
            },
            error: function(xhr, status, error){
                // re‑enable on AJAX error
                reenableBuildBoxButtons();
                alert('Error loading box products.');
            }
        });
    });

    jQuery(document.body).on('wc_backbone_modal_loaded', function(evt, templateId) {
        const text = document.querySelector('.hc-progress-text');
        text.textContent = `0 / ${boxSize}`;
    });

    jQuery(document).on('input change', '.wc-backbone-modal .qty', function() {
        const $modal        = jQuery(this).closest('.wc-backbone-modal');
        const $progressBar  = $modal.find('.hc-progress-bar');
        const $progressFill = $modal.find('.hc-progress-fill');
        const $progressText = $modal.find('.hc-progress-text');
        const $finishBtn    = $modal.find('#finish-box');
        const $footer       = $modal.find('footer');

        let totalCount = 0;
        $modal.find('.qty').each(function() {
            const count = parseInt(jQuery(this).val(), 10) || 0;
            totalCount += count;
        });

        // 🚨 Exceeded box size
        if (totalCount > boxSize) {
            $finishBtn.hide();

            if ($footer.find('.hc-footer-message').length === 0) {
                $footer.append(
                    '<div class="hc-footer-message" ' +
                    'style="color:white; display:block; width:100%; text-align:center; margin-top:10px;">' +
                    totalCount + ' selected, only select ' + boxSize +
                    '</div>'
                );
            } else {
                $footer.find('.hc-footer-message')
                    .text(totalCount + ' selected,' + ' only select ' + boxSize);
            }

            // stop here so later logic doesn’t re‑show the button
            return;
        } else {
            $finishBtn.show();
            $footer.find('.hc-footer-message').remove();
        }

        const clamped = Math.min(totalCount, boxSize);
        const percent = (clamped / boxSize) * 100;

        if (clamped >= boxSize) {
            $progressBar.css('display', 'none');
            $finishBtn.css('display', 'flex').removeClass('disabled');
            return;
        }

        $progressBar.css('display', 'block');
        $finishBtn.css('display', 'none').addClass('disabled');

        $progressFill.css('width', percent + '%');
        $progressText.text(`${clamped} / ${boxSize}`);
    });

    $(document).on('click', '#finish-box', function() {
        let selections = {};
        let totalCount = 0;

        const $modal = $(this).closest('.wc-backbone-modal');
        $modal.find('.qty').each(function() {
            const id    = $(this).data('product-id');
            const count = parseInt($(this).val(), 10) || 0;
            if (count > 0) {
                selections[id] = count;
                totalCount += count;
            }
        });

        if (totalCount !== boxSize) {
            alert(`Please select exactly ${boxSize} items.`);
            return;
        }

        // Close modal before firing add_to_cart
        $(document.body).trigger('wc_backbone_modal_close');

        $.post('/?wc-ajax=add_to_cart', {
            product_id: productId,
            quantity: 1,
            selections: JSON.stringify(selections),
            discount: boxDiscount
        }, function(response) {
            if (response && response.fragments) {
                $.each(response.fragments, function(selector, html) {
                    $(selector).replaceWith(html);
                });
            }
        }, 'json').fail(function(xhr) {
            console.error('AJAX request failed:', xhr.statusText);
        });
    });

    jQuery(document.body).on('wc_backbone_modal_close wc_backbone_modal_closed', function(){
        jQuery('.add_to_cart_button.disabled, .single_add_to_cart_button.disabled')
            .prop('disabled', false)
            .removeClass('disabled');
    });

    function reenableBuildBoxButtons() {
        jQuery('.add_to_cart_button.disabled, .single_add_to_cart_button.disabled')
            .prop('disabled', false)
            .removeClass('disabled');
    }
    // re‑enable when WooCommerce modal close fires
    jQuery(document).on('wc_backbone_modal_before_remove wc_backbone_modal_removed', function(e){
        reenableBuildBoxButtons();
    });
});


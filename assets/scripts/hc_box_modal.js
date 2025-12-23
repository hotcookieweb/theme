jQuery(function($){
    // ===============================
    // GLOBALS
    // ===============================
    let boxSize = 0;
    let productId = 0;


    // ===============================
    // DISABLE WOO AJAX FOR BOX PRODUCTS
    // ===============================
    jQuery(function($){
        $('.add_to_cart_button[data-box-size], .single_add_to_cart_button[data-box-size]')
            .removeClass('ajax_add_to_cart')
            .off('click');
    });


    // ===============================
    // SHOP PAGE — INTERCEPT CLICK
    // ===============================
    jQuery(document).on('click', '.add_to_cart_button[data-box-size]', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        jQuery(this).trigger('hc_buildabox');
    });


    // ===============================
    // SINGLE PRODUCT PAGE — INTERCEPT FORM SUBMIT
    // ===============================
    jQuery(document).on('submit', 'form.cart', function(e){

        const $btn = jQuery(this).find('.single_add_to_cart_button[data-box-size]');
        if (!$btn.length) return;

        // Prevent WooCommerce from adding to cart
        e.preventDefault();
        e.stopImmediatePropagation();

        // Only open modal if the user actually clicked the button
        if (e.originalEvent !== undefined) {
            $btn.trigger('hc_buildabox');
        }
    });

    // ===============================
    // UNIFIED BUILD-A-BOX HANDLER
    // ===============================
    jQuery(document).on(
        'hc_buildabox',
        '.add_to_cart_button[data-box-size], .single_add_to_cart_button[data-box-size]',
        function () {

            const $btn = jQuery(this);
            const $product = $btn.closest('.product');

            // Primary source: data attributes
            productId = parseInt($btn.data('product_id'), 10) ||
                        parseInt($btn.val(), 10) || 0;

            boxSize   = parseInt($btn.data('box-size'), 10) || 0;


            if (boxSize <= 0 || productId === 0) {
                alert('Product config error: missing box size or product ID');
                return;
            }

            $btn.prop('disabled', true).addClass('disabled');

            // 2. Fallbacks ONLY if needed (single product forms)
            if (!productId) {
                productId =
                    parseInt($product.find('[name="add-to-cart"]').val(), 10) ||
                    parseInt($product.find('.add_to_cart_button').data('product_id'), 10) ||
                    0;
            }

            // 3. Validate
            if (boxSize <= 0 || productId === 0) {
                alert('Product config error: missing box size or product ID');
                return;
            }

            // 4. Disable button
            $btn.prop('disabled', true).addClass('disabled');

            // 5. Load modal
            jQuery.ajax({
                url: hc_box_modal_params.ajax_url,
                data: { action: 'hc_get_box_products', box_size: boxSize, product_id: productId },
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        jQuery(document.body).WCBackboneModal({
                            template: 'hc-modal-add-box-products',
                            variable: response.data
                        });
                    }
                },
                error: function () {
                    reenableBuildBoxButtons();
                    alert('Error loading box products.');
                }
            });
        }
    );
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


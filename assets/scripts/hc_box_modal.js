jQuery(function($){
    // ===============================
    // GLOBALS
    // ===============================
    let boxSize = 0;
    let baseText = '';
    let productId = 0;
    let quantity = 1;
    let $btn = null;
    let $product = null;
    let lastClickedButton = null;

    // Track last clicked add-to-cart button because on form submit we can't easily tell
    $(document).on('click', '.single_add_to_cart_button, .add_to_cart_button', function() {
        lastClickedButton = $(this);
    });

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
    $(document).on('submit', 'form.cart', function(e) {
        const $clicked = lastClickedButton;

        // If clicked button has no box-size → normal add-to-cart
        if (!$clicked.data('box-size')) {
            return;
        }

        // Use the clicked button as the modal trigger
        $btn = $clicked;

        e.preventDefault();
        e.stopImmediatePropagation();

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

            $btn = jQuery(this);
            // Determine mode
            const mode = $btn.data('box-mode') || 'build';

            // If customize mode AND this is the add-to-cart button → do NOT open modal
            if (mode === 'customize' && !$btn.hasClass('customize-button')) {
                return; // allow normal add-to-cart behavior
            }

            $product = $btn.closest('.product');
            baseText = $baseText = $btn.text().trim();;

            // Primary source: data attributes
            productId = parseInt($btn.data('product_id'), 10) ||
                        parseInt($btn.val(), 10) || 0;

            boxSize   = parseInt($btn.data('box-size'), 10) || 0;

            quantity =
                parseInt($btn.data('quantity'), 10) ||                     // shop loop
                parseInt($product.find('input.qty').val(), 10) ||          // single product page1;                                                         // fallback
                1;                                                         // fallback


            if (boxSize <= 0 || productId === 0) {
                alert('Product config error: missing box size or product ID');
                return;
            }

            $btn.prop('disabled', true).addClass('disabled');

            // Disable button
            $btn.prop('disabled', true).addClass('disabled');

            // Load modal
            jQuery.ajax({
                url: hc_box_modal_params.ajax_url,
                data: { action: 'hc_get_modal_data', product_id: productId},
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
    // initial footer based on first qty input
    jQuery(document.body).on('wc_backbone_modal_loaded', function(event, modal) {
        // modal is a Backbone view; the element is modal.$el
        const $modal = modal.$el || jQuery('.wc-backbone-modal:visible');
        // Trigger your quantity logic once
        const $firstQty = $modal.find('.qty').first();
        if ($firstQty.length) {
            $firstQty.trigger('input'); // runs your entire footer/progress logic
        }
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
        const $title        = $modal.find('.modal-title');

        let totalCount = 0;
        let totalPrice = 0;

        $modal.find('.qty').each(function() {
            const $input = jQuery(this);
            const count  = parseInt($input.val(), 10) || 0;
            const price  = parseFloat($input.data('price')) || 0; // assumes data-price on input
            totalCount  += count;
            totalPrice  += count * price;
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
                    .text(totalCount + ' selected, only select ' + boxSize);
            }
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
        } else {
            $progressBar.css('display', 'block');
            $finishBtn.css('display', 'none').addClass('disabled');
            $progressFill.css('width', percent + '%');
            $progressText.text(`${clamped} / ${boxSize}`);
        }

        $title.text(`${baseText} ($${totalPrice.toFixed(2)})`);
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
        }                                                // fallback

        // Close modal before firing add_to_cart
        $(document.body).trigger('wc_backbone_modal_close');

        $.post('/?wc-ajax=add_to_cart', {
            product_id: productId,
            quantity: quantity,
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


<?php
    // === 1. Add "Populate From" Selector to Edit Attribute Screen ===
    add_action('woocommerce_after_edit_attribute_fields', function ($attribute) {
        $attribute_taxonomies = wc_get_attribute_taxonomies();
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="populate_from">Populate Terms From</label></th>
        <td>
            <select name="populate_from" id="populate_from">
                <option value="">— Select Attribute —</option>
                <?php foreach ($attribute_taxonomies as $taxonomy):
                        if ($taxonomy->attribute_id !== $attribute->attribute_id): ?>
	                        <option value="<?php echo esc_attr($taxonomy->attribute_name); ?>">
	                            <?php echo esc_html($taxonomy->attribute_label); ?>
	                        </option>
	                    <?php endif;
                            endforeach; ?>
            </select>
            <p class="description">This will clear all terms and copy from the selected attribute.</p>
        </td>
    </tr>
    <?php
        });

        // === 2. On Save, Repopulate Terms from Selected Attribute ===
        add_action('woocommerce_attribute_updated', function ($id, $data, $old_data) {
            if (! empty($_POST['populate_from'])) {
                $source_attr = sanitize_text_field($_POST['populate_from']);
                $target_attr = $data['attribute_name'];

                $source_tax = 'pa_' . $source_attr;
                $target_tax = 'pa_' . $target_attr;

                // Delete existing terms
                $existing_terms = get_terms(['taxonomy' => $target_tax, 'hide_empty' => false]);
                if (! is_wp_error($existing_terms)) {
                    foreach ($existing_terms as $term) {
                        wp_delete_term($term->term_id, $target_tax);
                    }
                }

                // Copy terms from source
                $source_terms = get_terms(['taxonomy' => $source_tax, 'hide_empty' => false]);
                if (! is_wp_error($source_terms)) {
                    foreach ($source_terms as $term) {
                        wp_insert_term($term->name, $target_tax, [
                            'slug'        => $term->slug,
                            'description' => $term->description,
                        ]);
                    }
                }
        }
    }, 10, 3);
    
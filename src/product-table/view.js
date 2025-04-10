/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */


/* eslint-enable no-console */
jQuery(document).ready(function($) {
    $(".add-to-cart-btn").click(function(event) {
        event.preventDefault(); 
        let button = $(this);
        let productId = button.data("product_id");
        let quantity = button.closest("tr").find(".quantity").val();
        console.log(productId, quantity);
        $.ajax({
            type: "POST",
            url: storeBlocksData.ajax_url, 
            data: {
                action: "store_blocks_add_to_cart",
                product_id: productId,
                quantity: quantity,
                nonce: storeBlocksData.nonce
            },
            
            beforeSend: function() {
                button.text("Adding...").prop("disabled", true);
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Error: Could not add to cart.");
            }
           
        });
    });
});




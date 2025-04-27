/* global jQuery, storeBlocksData */
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




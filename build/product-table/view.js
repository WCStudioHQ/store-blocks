jQuery(document).ready((function(t){t(".add-to-cart-btn").click((function(o){o.preventDefault();let a=t(this),e=a.data("product_id"),r=a.closest("tr").find(".quantity").val();console.log(e,r),t.ajax({type:"POST",url:storeBlocksData.ajax_url,data:{action:"store_blocks_add_to_cart",product_id:e,quantity:r,nonce:storeBlocksData.nonce},beforeSend:function(){a.text("Adding...").prop("disabled",!0)},success:function(t){t.success?window.location.reload():alert("Error: "+t.message)},error:function(){alert("Error: Could not add to cart.")}})}))}));
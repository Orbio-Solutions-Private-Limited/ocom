<?php

use Illuminate\Support\Facades\Event;
use Webkul\Product\Models\Product;

/**
 * Create a bundle with two required select options, each with two simple children.
 */
function createTwoOptionBundleForCart(): Product
{
    $product = test()->createBundleProduct([100, 200]);

    $locale = app()->getLocale();

    $secondOption = $product->bundle_options()->create([
        'type' => 'select',
        'is_required' => 1,
        'sort_order' => 1,
        $locale => ['label' => 'Second Option'],
    ]);

    foreach ([300, 400] as $index => $price) {
        $simple = test()->createSimpleProduct(['price' => ['float_value' => $price]]);

        test()->setProductStock($simple, 10);

        $secondOption->bundle_option_products()->create([
            'product_id' => $simple->id,
            'qty' => 1,
            'sort_order' => $index,
            'is_default' => $index === 0 ? 1 : 0,
        ]);
    }

    Event::dispatch('catalog.product.update.after', $product);

    return $product->fresh()->load('bundle_options.bundle_option_products');
}

// ============================================================================
// Adding A Bundle Product
// ============================================================================

it('should add a bundle to the cart when bundle option ids are submitted as scalars', function () {
    $product = createTwoOptionBundleForCart();

    $options = $product->bundle_options->sortBy('sort_order')->values();

    $firstOptionProduct = $options[0]->bundle_option_products->firstWhere('is_default', 0);
    $secondOptionProduct = $options[1]->bundle_option_products->firstWhere('is_default', 0);

    $this->addProductToCart($product->id, 1, [
        'bundle_options' => [
            $options[0]->id => $firstOptionProduct->id,
            $options[1]->id => $secondOptionProduct->id,
        ],
        'bundle_option_qty' => [
            $options[0]->id => 1,
            $options[1]->id => 1,
        ],
    ])->assertOk()
        ->assertJsonPath('message', trans('shop::app.checkout.cart.item-add-to-cart'));
});

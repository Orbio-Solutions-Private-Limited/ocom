<?php

use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Product\Models\Product;

/**
 * Create a bundle with two required select options, each with two simple children.
 */
function createTwoOptionBundle(): Product
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

    return $product->fresh()->load('bundle_options.bundle_option_products.product');
}

// ============================================================================
// Bundle Option Payload
// ============================================================================

it('should normalize scalar bundle option ids before preparing a cart line', function () {
    $product = createTwoOptionBundle();

    $options = $product->bundle_options->sortBy('sort_order')->values();

    $firstOptionProduct = $options[0]->bundle_option_products->firstWhere('is_default', 0);
    $secondOptionProduct = $options[1]->bundle_option_products->firstWhere('is_default', 0);

    Cart::setCart(CartModel::factory()->create());

    $type = $product->getTypeInstance();

    $lines = $type->prepareForCart([
        'product_id' => $product->id,
        'quantity' => 1,
        'bundle_options' => [
            $options[0]->id => $firstOptionProduct->id,
            $options[1]->id => $secondOptionProduct->id,
        ],
        'bundle_option_qty' => [
            $options[0]->id => 1,
            $options[1]->id => 1,
        ],
    ]);

    expect($lines)->toBeArray()
        ->and($lines[0]['type'])->toBe('bundle')
        ->and(count($lines))->toBeGreaterThan(1);
});

it('should prepare a cart line when bundle option ids are already nested arrays', function () {
    $product = createTwoOptionBundle();

    $options = $product->bundle_options->sortBy('sort_order')->values();

    $firstOptionProduct = $options[0]->bundle_option_products->first();
    $secondOptionProduct = $options[1]->bundle_option_products->first();

    Cart::setCart(CartModel::factory()->create());

    $type = $product->getTypeInstance();

    $lines = $type->prepareForCart([
        'product_id' => $product->id,
        'quantity' => 1,
        'bundle_options' => [
            $options[0]->id => [$firstOptionProduct->id],
            $options[1]->id => [$secondOptionProduct->id],
        ],
        'bundle_option_qty' => [
            $options[0]->id => 1,
            $options[1]->id => 1,
        ],
    ]);

    expect($lines)->toBeArray()
        ->and($lines[0]['type'])->toBe('bundle');
});

it('should resolve child products when bundle option ids are submitted as scalars', function () {
    $product = test()->createBundleProduct([100, 200]);

    $option = $product->bundle_options->first();

    $nonDefault = $option->bundle_option_products->firstWhere('is_default', 0);

    $type = $product->getTypeInstance();

    $normalized = $type->validateBundleOptionForCart([
        $option->id => $nonDefault->id,
    ]);

    $children = $type->getCartChildProducts([
        'bundle_options' => $normalized,
        'bundle_option_qty' => [$option->id => 1],
    ]);

    expect($children)->toHaveKey($nonDefault->product_id);
});

it('should build additional options when bundle option ids are submitted as scalars', function () {
    $product = test()->createBundleProduct([100, 200]);

    $option = $product->bundle_options->first();

    $nonDefault = $option->bundle_option_products->firstWhere('is_default', 0);

    $type = $product->getTypeInstance();

    $normalized = $type->validateBundleOptionForCart([
        $option->id => $nonDefault->id,
    ]);

    $additional = $type->getAdditionalOptions([
        'bundle_options' => $normalized,
        'bundle_option_qty' => [$option->id => 1],
    ]);

    expect($additional['attributes'])->not->toBeEmpty();
});

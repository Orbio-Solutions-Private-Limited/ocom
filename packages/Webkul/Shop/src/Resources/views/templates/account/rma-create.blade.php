@php
    use Webkul\RMA\Enums\DefaultRMAResolution;
@endphp

<div class="bg-background box border-none shadow-sm">
    <div class="border-b p-4">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-secondary text-2xl">
                @lang('shop::app.rma.customer.create.heading')
            </h1>

            <x-shop::ui.button
                color="secondary"
                variant="soft"
                size="sm"
                href="{{ $order
                    ? route('shop.customers.account.rma.create')
                    : route('shop.customers.account.rma.index') }}"
            >
                @lang('shop::app.checkout.onepage.address.back')
            </x-shop::ui.button>
        </div>
    </div>

    @if (! $order)
        <x-shop::datagrid :src="route('shop.customers.account.rma.create')" />
    @elseif ($orderItems->isEmpty())
        <p class="text-danger p-6 text-center font-medium">
            @lang('shop::app.rma.customer.create.rma-not-available-quotes')
        </p>
    @else
        <div class="space-y-6 p-4">
            @foreach ($orderItems as $item)
                <div class="border-on-background/10 rounded-lg border p-4">
                    <div class="mb-4 flex gap-4">
                        @if (! empty($item->base_image_url))
                            <img
                                src="{{ $item->base_image_url }}"
                                alt="{{ $item->name }}"
                                class="h-20 w-20 rounded-sm object-cover"
                            />
                        @endif

                        <div class="space-y-1 text-sm">
                            <p class="text-base font-medium">{{ $item->name }}</p>

                            @foreach (array_filter(is_array($item->attributes) ? $item->attributes : []) as $attribute)
                                <p>
                                    {{ $attribute['attribute_name'] }}:
                                    <span class="font-medium">{{ $attribute['option_label'] }}</span>
                                </p>
                            @endforeach

                            <p>
                                @lang('shop::app.customers.account.rma.create.sku'):
                                <span class="font-medium">{{ $item->sku }}</span>
                            </p>

                            <p>
                                @lang('shop::app.customers.account.rma.create.price'):
                                <span class="font-medium">{{ core()->formatPrice($item->price) }}</span>
                            </p>

                            <p>
                                @lang('shop::app.customers.account.rma.create.current-order-quantity'):
                                <span class="font-medium">{{ $item->currentQuantity }}</span>
                            </p>
                        </div>
                    </div>

                    @if ($item->currentQuantity <= 0)
                        <p class="text-danger text-sm">
                            @lang('shop::app.customers.account.rma.create.product-already-raw')
                        </p>
                    @else
                        <form
                            method="POST"
                            action="{{ route('shop.customers.account.rma.store') }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                            x-data="{
                                resolution: @js(old('resolution_type', '')),
                                reasons: @js($reasonsByResolution),
                            }"
                        >
                            @csrf

                            <input type="hidden" name="order_id" value="{{ $order->id }}" />
                            <input type="hidden" name="order_item_id" value="{{ $item->order_item_id }}" />

                            <div class="grid gap-4 md:grid-cols-2">
                                <x-shop::ui.form.select
                                    required
                                    name="resolution_type"
                                    x-model="resolution"
                                    :label="trans('shop::app.customers.account.rma.create.resolution-type')"
                                >
                                    <option value="">
                                        @lang('shop::app.customers.account.rma.create.select')
                                    </option>

                                    @if ($item->forReturnQuantity > 0)
                                        <option value="{{ DefaultRMAResolution::RETURN->value }}">
                                            @lang('shop::app.customers.account.rma.create.return')
                                        </option>
                                    @endif

                                    @if ($item->forCancelQuantity > 0)
                                        <option value="{{ DefaultRMAResolution::CANCEL_ITEMS->value }}">
                                            @lang('shop::app.customers.account.rma.create.cancel-items')
                                        </option>
                                    @endif
                                </x-shop::ui.form.select>

                                <x-shop::ui.form.select
                                    required
                                    name="rma_reason_id"
                                    x-bind:disabled="! resolution"
                                    :label="trans('shop::app.rma.customer.create.reason')"
                                >
                                    <option value="">
                                        @lang('shop::app.customers.account.rma.create.select')
                                    </option>

                                    <template x-for="reason in (reasons[resolution] || [])" :key="reason.id">
                                        <option
                                            :value="reason.id"
                                            x-text="reason.title"
                                            :selected="String(reason.id) === @js((string) old('rma_reason_id'))"
                                        ></option>
                                    </template>
                                </x-shop::ui.form.select>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <x-shop::ui.form.input
                                    required
                                    type="number"
                                    name="rma_qty"
                                    min="1"
                                    x-bind:max="resolution === @js(DefaultRMAResolution::CANCEL_ITEMS->value)
                                        ? {{ (int) $item->forCancelQuantity }}
                                        : {{ (int) $item->forReturnQuantity }}"
                                    :value="old('rma_qty')"
                                    :label="trans('shop::app.rma.customer.rma-qty')"
                                />

                                <div x-show="resolution === @js(DefaultRMAResolution::RETURN->value)">
                                    <x-shop::ui.form.select
                                        name="package_condition"
                                        :label="trans('shop::app.customers.account.rma.create.package-condition')"
                                    >
                                        <option value="">
                                            @lang('shop::app.customers.account.rma.create.select')
                                        </option>

                                        <option
                                            value="open"
                                            @selected(old('package_condition') === 'open')
                                        >
                                            @lang('shop::app.customers.account.rma.create.open')
                                        </option>

                                        <option
                                            value="packed"
                                            @selected(old('package_condition') === 'packed')
                                        >
                                            @lang('shop::app.customers.account.rma.create.packed')
                                        </option>
                                    </x-shop::ui.form.select>
                                </div>
                            </div>

                            @foreach ($customFields as $field)
                                @include('shop::templates.account.rma-custom-field', ['field' => $field])
                            @endforeach

                            <x-shop::ui.form.textarea
                                name="information"
                                rows="4"
                                maxlength="250"
                                :label="trans('shop::app.rma.customer.create.information')"
                            >{{ old('information') }}</x-shop::ui.form.textarea>

                            <x-shop::ui.form.input
                                type="file"
                                name="images[]"
                                multiple
                                accept="{{ core()->getConfigData('sales.rma.setting.allowed_file_extension') }}"
                                :label="trans('shop::app.customers.account.rma.create.images')"
                            />

                            <div class="flex flex-wrap items-center gap-2">
                                <label class="flex items-start gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="agreement"
                                        value="1"
                                        required
                                        class="mt-1"
                                        @checked(old('agreement'))
                                    />

                                    <span>
                                        @lang('shop::app.customers.account.rma.terms.terms')
                                    </span>
                                </label>

                                <x-shop::ui.modal>
                                    <x-slot:trigger>
                                        <button
                                            type="button"
                                            class="text-primary text-sm underline"
                                        >
                                            @lang('shop::app.customers.account.rma.terms.read')
                                        </button>
                                    </x-slot>

                                    <x-shop::ui.modal.title>
                                        @lang('installer::app.seeders.cms.pages.terms-conditions.title')
                                    </x-shop::ui.modal.title>

                                    <div class="mt-4 max-h-96 overflow-y-auto text-sm">
                                        {{ core()->getConfigData('sales.rma.setting.return_policy') }}
                                    </div>
                                </x-shop::ui.modal>
                            </div>

                            <x-shop::ui.form.error name="agreement" />

                            <x-shop::ui.button
                                type="submit"
                                color="primary"
                            >
                                @lang('shop::app.rma.customer.create.save')
                            </x-shop::ui.button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

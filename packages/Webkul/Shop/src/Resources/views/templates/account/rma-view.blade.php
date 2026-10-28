@php
    use Webkul\RMA\Enums\DefaultRMAResolution;

    $item = $rma->item;
    $customerName = auth()->guard('customer')->user()->name;
@endphp

<div class="space-y-6">
    <div class="bg-background box border-none shadow-sm">
        <div class="border-b p-4">
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-secondary text-2xl">
                    @lang('shop::app.customers.account.rma.view.id') #{{ $rma->id }}
                </h1>

                <x-shop::ui.button
                    color="secondary"
                    variant="soft"
                    size="sm"
                    href="{{ route('shop.customers.account.rma.index') }}"
                >
                    @lang('shop::app.checkout.onepage.address.back')
                </x-shop::ui.button>
            </div>
        </div>

        <div class="space-y-4 p-4 text-sm">
            <div class="grid gap-1 md:grid-cols-[12rem_1fr]">
                <span class="font-medium">
                    @lang('shop::app.rma.view-customer-rma-content.request-on')
                </span>
                <span>{{ \Carbon\Carbon::parse($rma->created_at)->format('F j, Y, h:i:s A') }}</span>
            </div>

            <div class="grid gap-1 md:grid-cols-[12rem_1fr]">
                <span class="font-medium">
                    @lang('shop::app.rma.view-customer-rma.order-id')
                </span>
                <a
                    href="{{ route('shop.customers.account.orders.view', $rma->order_id) }}"
                    class="text-primary underline"
                >
                    #{{ $rma->order_id }}
                </a>
            </div>

            @foreach ($rma->additionalFields as $field)
                @if ($field->customField)
                    <div class="grid gap-1 md:grid-cols-[12rem_1fr]">
                        <span class="font-medium">{{ $field->customField->label }}</span>
                        <span>{{ $field->value }}</span>
                    </div>
                @endif
            @endforeach

            @if (! empty($rma->information))
                <div class="grid gap-1 md:grid-cols-[12rem_1fr]">
                    <span class="font-medium">
                        @lang('shop::app.rma.view-customer-rma.additional-information')
                    </span>
                    <span>{{ $rma->information }}</span>
                </div>
            @endif

            @if ($rma->images->isNotEmpty())
                <div class="grid gap-1 md:grid-cols-[12rem_1fr]">
                    <span class="font-medium">
                        @lang('shop::app.rma.view-customer-rma.images')
                    </span>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($rma->images as $image)
                            <a
                                href="{{ route('shop.customers.account.rma.image', $image->id) }}"
                                target="_blank"
                            >
                                <img
                                    src="{{ route('shop.customers.account.rma.image', $image->id) }}"
                                    alt=""
                                    class="h-20 w-20 rounded-sm object-cover"
                                />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid gap-1 md:grid-cols-[12rem_1fr]">
                <span class="font-medium">
                    @lang('shop::app.rma.view-customer-rma-content.rma-status')
                </span>
                <span
                    class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-medium"
                    style="background: {{ $rmaStatus->color }}20; color: {{ $rmaStatus->color }}"
                >
                    {{ $rmaStatus->title }}
                </span>
            </div>
        </div>

        @if (
            ! $isExpired
            && ($canCloseRma || $canReopenRma)
        )
            <div class="border-t p-4">
                <form
                    method="POST"
                    action="{{ $canCloseRma
                        ? route('shop.customers.account.rma.update_status', $rma->id)
                        : route('shop.customers.account.rma.re_open', $rma->id) }}"
                    class="flex flex-wrap items-center justify-between gap-4"
                >
                    @csrf

                    @php $checkboxName = $canCloseRma ? 'close_rma' : 'reopen_rma'; @endphp

                    <label class="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="{{ $checkboxName }}"
                            value="1"
                            required
                        />

                        <span>
                            {{ $canCloseRma
                                ? trans('shop::app.rma.view-customer-rma.status-quotes')
                                : trans('shop::app.rma.customer.create.reopen-request') }}
                        </span>
                    </label>

                    <x-shop::ui.button type="submit" color="primary" size="sm">
                        @lang('shop::app.rma.view-customer-rma.save-btn')
                    </x-shop::ui.button>
                </form>
            </div>
        @endif
    </div>

    <div class="bg-background box border-none shadow-sm">
        <div class="border-b p-4">
            <h2 class="text-secondary text-xl">
                @lang('shop::app.rma.view-customer-rma.items-request')
            </h2>
        </div>

        <div class="p-4">
            @if ($item)
                <div class="flex gap-4 text-sm">
                    @php
                        $product = $item->orderItem->product ?? null;
                        $productImage = $product ? product_image()->getProductBaseImage($product) : [];
                    @endphp

                    @if (! empty($productImage['small_image_url']))
                        <img
                            src="{{ $productImage['small_image_url'] }}"
                            alt="{{ $productImage['alt'] ?? '' }}"
                            class="h-16 w-16 rounded-sm object-cover"
                        />
                    @endif

                    <div class="space-y-1">
                        <p class="font-medium">{{ $item->orderItem->name }}</p>

                        <p>
                            @lang('shop::app.rma.table-heading.sku'):
                            {{ $item->orderItem->product->sku ?? $item->orderItem->sku }}
                        </p>

                        <p>
                            @lang('shop::app.rma.table-heading.price'):
                            {{ core()->formatPrice($item->orderItem->price, $rma->order->order_currency_code) }}
                        </p>

                        <p>
                            @lang('shop::app.rma.table-heading.rma-qty'):
                            {{ $item->quantity }} / {{ $item->orderItem->qty_ordered }}
                        </p>

                        <p>
                            @lang('shop::app.rma.table-heading.resolution-type'):
                            @if ($item->resolution == DefaultRMAResolution::RETURN->value)
                                @lang('shop::app.customers.account.rma.create.return')
                            @else
                                @lang('shop::app.customers.account.rma.create.cancel-items')
                            @endif
                        </p>

                        <p>
                            @lang('shop::app.rma.table-heading.reason'):
                            {{ $item->reason->title ?? '' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-background box border-none shadow-sm">
        <div class="border-b p-4">
            <h2 class="text-secondary text-xl">
                @lang('shop::app.rma.view-customer-rma.conversations')
            </h2>
        </div>

        <div class="max-h-80 space-y-3 overflow-y-auto p-4">
            @forelse ($rma->messages->sortBy('id') as $message)
                <div class="{{ $message->is_admin ? 'mr-12' : 'ml-12' }} rounded-lg p-3 text-sm {{ $message->is_admin ? 'bg-surface' : 'bg-primary/10' }}">
                    <div class="mb-1 flex items-center gap-2 text-xs font-medium">
                        <span>
                            {{ $message->is_admin
                                ? trans('shop::app.rma.view-customer-rma.admin')
                                : $customerName }}
                        </span>

                        <span class="opacity-70">
                            {{ \Carbon\Carbon::parse($message->created_at)->format('F j, Y, h:i A') }}
                        </span>
                    </div>

                    <p>{{ $message->message }}</p>

                    @if ($message->attachment)
                        <a
                            href="{{ $message->attachment_url }}"
                            class="text-primary mt-2 inline-block text-xs underline"
                        >
                            {{ $message->attachment }}
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-on-background/60 text-center text-sm">
                    @lang('shop::app.rma.conversation-texts.no-record')
                </p>
            @endforelse
        </div>

        <form
            method="POST"
            action="{{ route('shop.customers.account.rma.send_message') }}"
            enctype="multipart/form-data"
            class="space-y-3 border-t p-4"
        >
            @csrf

            <input type="hidden" name="rma_id" value="{{ $rma->id }}" />

            <x-shop::ui.form.textarea
                required
                name="message"
                rows="3"
                maxlength="250"
                :label="trans('shop::app.customers.account.rma.view.enter-message')"
            ></x-shop::ui.form.textarea>

            <x-shop::ui.form.input
                type="file"
                name="file"
                :label="trans('shop::app.customers.account.rma.view.add-attachments')"
            />

            <x-shop::ui.button type="submit" color="primary" size="sm">
                @lang('shop::app.customers.account.rma.view.send-message-btn')
            </x-shop::ui.button>
        </form>
    </div>
</div>

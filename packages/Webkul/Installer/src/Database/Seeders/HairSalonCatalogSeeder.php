<?php

namespace Webkul\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Helpers\MediaFileName;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductRepository;

class HairSalonCatalogSeeder extends Seeder
{
    /**
     * Path to the hair salon catalog JSON file.
     */
    const DATA_FILE = __DIR__.'/../../Data/hair-salon-catalog.json';

    /**
     * Map of category slug to category id.
     *
     * @var array<string, int>
     */
    protected array $categoryIds = [];

    /**
     * Default channel code.
     */
    protected string $channelCode;

    /**
     * Default locale code.
     */
    protected string $localeCode;

    /**
     * Inventory source used for simple products.
     */
    protected int $inventorySourceId;

    /**
     * Create a new seeder instance.
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ProductImageRepository $productImageRepository,
        protected InventorySourceRepository $inventorySourceRepository,
        protected MediaFileName $mediaFileName,
    ) {}

    /**
     * Seed hair salon categories and products without wiping the existing catalog.
     */
    public function run(): void
    {
        $catalog = json_decode(File::get(self::DATA_FILE), true, flags: JSON_THROW_ON_ERROR);

        $this->channelCode = core()->getDefaultChannelCode();
        $this->localeCode = core()->getDefaultLocaleCodeFromDefaultChannel();
        $this->inventorySourceId = (int) $this->inventorySourceRepository->first()->id;

        $rootId = (int) core()->getDefaultChannel()->root_category_id;

        foreach ($catalog['categories'] as $category) {
            $this->seedCategoryTree($category, $rootId);
        }

        $created = 0;
        $updated = 0;

        foreach ($catalog['products'] as $product) {
            $existed = (bool) $this->productRepository->findOneByField('sku', $product['sku']);

            $this->seedProduct($product);

            if ($existed) {
                $updated++;
            } else {
                $created++;
            }
        }

        $this->command?->info(sprintf(
            'Hair salon catalog: %d categories, %d products created, %d products updated.',
            count($this->categoryIds),
            $created,
            $updated
        ));
    }

    /**
     * Create or reuse a category and recurse into its children.
     */
    protected function seedCategoryTree(array $data, int $parentId): void
    {
        $category = $this->categoryRepository->findBySlug($data['slug']);

        if (! $category) {
            Event::dispatch('catalog.category.create.before');

            $category = $this->categoryRepository->create($this->categoryPayload($data, $parentId));

            Event::dispatch('catalog.category.create.after', $category);
        }

        $this->categoryIds[$data['slug']] = $category->id;

        $this->ensureCategoryLogo($category, $data['name'], $data['color']);

        foreach ($data['children'] ?? [] as $child) {
            $this->seedCategoryTree($child, $category->id);
        }
    }

    /**
     * Build the payload used to create a category in every locale.
     */
    protected function categoryPayload(array $data, int $parentId): array
    {
        $payload = [
            'locale' => 'all',
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'meta_title' => $data['name'],
            'meta_keywords' => 'hair salon, '.$data['name'],
            'meta_description' => trim(strip_tags($data['description'])),
            'parent_id' => $parentId,
            'position' => $data['position'],
            'display_mode' => 'products_and_description',
            'status' => 1,
            'logo_path' => [],
            'banner_path' => [],
        ];

        return $payload;
    }

    /**
     * Create or update a catalog product from the JSON definition.
     */
    protected function seedProduct(array $data): void
    {
        $product = $this->productRepository->findOneByField('sku', $data['sku']);

        if (! $product) {
            Event::dispatch('catalog.product.create.before');

            $product = $this->productRepository->create([
                'type' => $data['type'],
                'attribute_family_id' => 1,
                'sku' => $data['sku'],
            ]);

            Event::dispatch('catalog.product.create.after', $product);
        }

        Event::dispatch('catalog.product.update.before', $product->id);

        $product = $this->productRepository->update($this->productPayload($data), $product->id);

        Event::dispatch('catalog.product.update.after', $product);

        if ($product->images->isEmpty()) {
            $this->attachProductImage($product, $data['name'], $data['color']);
        }
    }

    /**
     * Build the product update payload, including inventory for simple types.
     */
    protected function productPayload(array $data): array
    {
        $payload = [
            'sku' => $data['sku'],
            'url_key' => $data['sku'],
            'name' => $data['name'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'price' => $data['price'],
            'product_number' => $data['sku'],
            'meta_title' => $data['name'],
            'meta_keywords' => 'hair salon, '.$data['name'],
            'meta_description' => $data['short_description'],
            'channel' => $this->channelCode,
            'locale' => $this->localeCode,
            'status' => 1,
            'visible_individually' => 1,
            'new' => ! empty($data['new']) ? 1 : 0,
            'featured' => ! empty($data['featured']) ? 1 : 0,
            'guest_checkout' => 1,
            'manage_stock' => $data['type'] === 'simple' ? 1 : 0,
            'categories' => $this->resolveCategoryIds($data['categories']),
            'channels' => [core()->getDefaultChannel()->id],
        ];

        if ($data['type'] === 'simple') {
            $payload['weight'] = $data['weight'] ?? 0.5;
            $payload['inventories'] = [
                $this->inventorySourceId => $data['qty'] ?? 20,
            ];
        }

        return $payload;
    }

    /**
     * Map category slugs from the JSON file to seeded category ids.
     *
     * @param  string[]  $slugs
     * @return int[]
     */
    protected function resolveCategoryIds(array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug) => $this->categoryIds[$slug] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Store a colour swatch as the category logo when none exists.
     */
    protected function ensureCategoryLogo($category, string $title, string $color): void
    {
        if ($category->logo_path) {
            return;
        }

        $file = $this->makeSwatchFile($title, $color);
        $encoded = image_manager()->fromPath($file)->toWebp()->toBytes();
        $path = $this->mediaFileName->resolve('categories/'.$category->id, Str::slug($title), 'webp');

        Storage::put($path, (string) $encoded);

        $category->logo_path = $path;
        $category->save();

        File::delete($file);
    }

    /**
     * Attach a generated swatch image to a product that has no media yet.
     */
    protected function attachProductImage($product, string $title, string $color): void
    {
        $file = $this->makeSwatchFile($title, $color);

        $this->productImageRepository->upload([
            'images' => [
                'files' => [
                    new UploadedFile($file, basename($file), 'image/jpeg', null, true),
                ],
                'meta' => [
                    ['alt_text' => $title, 'file_name' => Str::slug($title)],
                ],
            ],
        ], $product, 'images');

        File::delete($file);
    }

    /**
     * Write a temporary JPEG colour swatch labelled with the given title.
     */
    protected function makeSwatchFile(string $title, string $hex): string
    {
        $path = sys_get_temp_dir().'/salon-'.Str::slug($title).'-'.uniqid().'.jpg';
        $image = imagecreatetruecolor(800, 800);
        [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');
        imagefilledrectangle($image, 0, 0, 800, 800, imagecolorallocate($image, $red, $green, $blue));

        $label = imagecolorallocate($image, 255, 255, 255);
        $lines = $this->wrapLabel($title, 28);
        $lineHeight = 22;
        $startY = (int) ((800 - (count($lines) * $lineHeight)) / 2);

        foreach ($lines as $index => $line) {
            $width = imagefontwidth(5) * strlen($line);
            imagestring($image, 5, (int) ((800 - $width) / 2), $startY + ($index * $lineHeight), $line, $label);
        }

        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $path;
    }

    /**
     * Split a title into lines that fit the built-in GD font.
     *
     * @return string[]
     */
    protected function wrapLabel(string $title, int $maxChars): array
    {
        $words = preg_split('/\s+/', $title) ?: [$title];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if (strlen($candidate) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}

<?php

namespace Database\Seeders;

use App\Enums\OptionStatus;
use App\Enums\OptionType;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (File::files(storage_path('app/products')) as $file) {
            $product = json_decode($file->getContents());

            $product_db = Product::updateOrCreate([
                'name' => $product->name,
            ], [
                'description' => $this->description($product->description),
                'images' => $this->images($product->images, $product->name),
                'videos' => $this->videos($product->videos),
                'enabled' => $product->enabled,
                'visibility' => ProductVisibility::keyForValue($product->visibility),
                'type' => ProductType::keyForValue($product->type),
                'manufacturer' => $product->manufacturer,
                'specifications' => $product->specifications,
            ]);

            foreach ($product->options as $option) {
                $product_db->options()->updateOrCreate([
                    'sku' => $option->sku,
                ], [
                    'price' => $option->price,
                    'value_added_tax' => $option->value_added_tax,
                    'images' => $this->images($option->images),
                    'color' => $option->color ?? null,
                    'version' => $option->version ?? null,
                    'volume' => $option->volume ?? null,
                    'type' => OptionType::keyForValue($option->type),
                    'status' => OptionStatus::keyForValue($option->status),
                    'quantity' => $option->quantity,
                    'weight' => $option->weight ?? null,
                    'length' => $option->length ?? null,
                    'width' => $option->width ?? null,
                    'height' => $option->height ?? null,
                    'specifications' => $option->specifications,
                ]);
            }

            $product_db->categories()->saveMany(
                Category::whereIn('name', $product->categories)->get()
            );

            $description = $product->description[0]->description;

            if ($description) {
                $product_db->seo()->updateOrCreate([
                ], [
                    'description' => mb_substr($description, 0, 159),
                ]);
            }
        }
    }

    protected function description(array $description): string
    {
        $result = '';

        foreach ($description as $section) {
            if ($section->title) {
                $result .= "<h3>$section->title</h3>";
            }

            if ($section->description) {
                $result .= "<p>$section->description</p>";
            }

            foreach ($section->contents as $content) {
                if ($content->title) {
                    $result .= "<h4>$content->title</h4>";
                }

                if ($content->description) {
                    $result .= "<p>$content->description</p>";
                }

                if ($content->image) {
                    store_image(
                        config('filesystems.disks.product.root'),
                        $content->image
                    );

                    $src = product_image_url($content->image);
                    $alt = mb_substr(
                        $content->title ??
                        $content->description ??
                        $section->description ??
                        $section->title,
                        0,
                        49
                    );

                    $result .= '<p><img style="display: block; margin-left: auto; margin-right: auto;" src="'.$src.'" alt="'.$alt.'" width="275"></p>';
                }
            }
        }

        return mb_substr($result, 0, 4294967294);
    }

    protected function images(array $images, ?string $product_name = null): string
    {
        foreach ($images as $image) {
            store_image(
                config('filesystems.disks.product.root'),
                $image
            );
        }

        return json_encode(
            array_map(
                fn (string $image): array|string => $product_name
                    ? [
                        'image' => $image,
                        'alt' => mb_substr($product_name, 0, 49),
                    ]
                    : $image,
                $images
            ),
            JSON_UNESCAPED_UNICODE
        );
    }

    protected function videos(array $videos): string
    {
        return json_encode(
            array_map(
                function (object $video): array {
                    $video->video = json_encode($video->video, JSON_UNESCAPED_UNICODE);
                    $video->image = null;
                    $video->title = null;
                    $video->description = null;

                    return (array) $video;
                },
                $videos
            ),
            JSON_UNESCAPED_UNICODE
        );
    }
}

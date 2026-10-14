<?php

namespace Database\Seeders;

use App\Enums\ProductIllustration;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeds the SAXIY catalog that used to be hardcoded in the storefront. Prices are left empty for admins to fill in.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        $categories = collect([
            ['key' => 'kurt', 'color' => '#1660C9',
                'name' => ['uz' => 'Bio qurt', 'ru' => 'Био курт', 'en' => 'Bio kurt'],
                'description' => [
                    'uz' => 'Sutli, rezavorli va assorti — tayyor blisterlarda. Kassa zonasi va sovutgichli vitrinalar uchun qulay.',
                    'ru' => 'Молочный, ягодный и ассорти в готовых блистерах. Удобно ставить на кассовую зону и в холодильные витрины.',
                    'en' => 'Milk, berry and assorted in ready-to-shelf blisters. Fits checkout zones and chilled displays.',
                ]],
            ['key' => 'classic', 'color' => '#159E96',
                'name' => ['uz' => 'Klassik qurt', 'ru' => 'Классический курт', 'en' => 'Classic kurt'],
                'description' => [
                    'uz' => "An'anaviy qurt — donalab va uzun qadoqda.",
                    'ru' => 'Традиционный курт — на развес и в штучной упаковке.',
                    'en' => 'Traditional kurt, sold as single pieces or long sticks.',
                ]],
            ['key' => 'nuts', 'color' => '#F2B705',
                'name' => ['uz' => "Yong'oqlar", 'ru' => 'Орехи', 'en' => 'Nuts'],
                'description' => [
                    'uz' => "Shaffof 30 va 60 g paketlardagi qovurilgan mag'izlar. Besh ta'm, bitta format.",
                    'ru' => 'Обжаренные ядра в прозрачных пакетах 30 и 60 г. Пять вкусов, один формат.',
                    'en' => 'Roasted kernels in clear 30 g and 60 g bags. Five flavours, one format.',
                ]],
            ['key' => 'suluguni', 'color' => '#F2A900',
                'name' => ['uz' => 'Suluguni', 'ru' => 'Сулугуни', 'en' => 'Suluguni'],
                'description' => [
                    'uz' => "Cho'ziluvchan pishloq tayoqchalari uch o'lchamda — gazakdan oilaviy qadoqqacha.",
                    'ru' => 'Тягучий сыр-соломка в трёх размерах — от закуски до семейной пачки.',
                    'en' => 'Stringy cheese sticks in three sizes, from a snack to a family pack.',
                ]],
        ])->mapWithKeys(fn (array $attributes, int $index): array => [
            $attributes['key'] => Category::create([
                ...collect($attributes)->except('key')->all(),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]),
        ]);

        $kurtDescription = [
            'uz' => "Tarkibi: sut, yodlangan tuz.\nOziqaviy qiymati (100 g): oqsillar 17 g, uglevodlar 1 g, yog'lar 5 g.\nSaqlash muddati: 6 oy.",
            'ru' => "Состав: молоко, йодированная соль.\nПищевая ценность (100 г): белки 17 г, углеводы 1 г, жиры 5 г.\nСрок годности: 6 месяцев.",
            'en' => "Ingredients: milk, iodised salt.\nNutrition (100 g): protein 17 g, carbohydrates 1 g, fat 5 g.\nShelf life: 6 months.",
        ];

        $milk = ['uz' => 'Bio qurt, sutli', 'ru' => 'Био курт молочный', 'en' => 'Bio kurt, milk'];
        $nuts = [
            'peanut' => [ProductIllustration::Peanut, ['uz' => "Yeryong'oq", 'ru' => 'Арахис', 'en' => 'Peanut']],
            'cashew' => [ProductIllustration::Cashew, ['uz' => 'Keshyu', 'ru' => 'Кешью', 'en' => 'Cashew']],
            'almond' => [ProductIllustration::Almond, ['uz' => 'Bodom', 'ru' => 'Миндаль', 'en' => 'Almond']],
            'pista' => [ProductIllustration::Pistachio, ['uz' => 'Pista', 'ru' => 'Фисташки', 'en' => 'Pistachio']],
            'pit' => [ProductIllustration::ApricotKernels, ['uz' => "O'rik danagi", 'ru' => 'Косточки', 'en' => 'Apricot kernels']],
        ];

        $products = [
            ['bk-30-40', 'kurt', 30, 40, ProductIllustration::KurtBalls, $milk, true],
            ['bk-47-30', 'kurt', 47, 30, ProductIllustration::KurtBalls, $milk],
            ['bk-30-30sq', 'kurt', 30, 30, ProductIllustration::KurtBars, ['uz' => 'Bio qurt, sutli briket', 'ru' => 'Био курт молочный, брикет', 'en' => 'Bio kurt, milk bars']],
            ['bk-30-40mix', 'kurt', 30, 40, ProductIllustration::KurtBallsMix, ['uz' => 'Bio qurt, assorti', 'ru' => 'Био курт ассорти', 'en' => 'Bio kurt, assorted'], true],
            ['bk-60-30', 'kurt', 60, 30, ProductIllustration::KurtBalls, $milk],
            ['bk-60-30berry', 'kurt', 60, 30, ProductIllustration::KurtBallsBerry, ['uz' => 'Bio qurt, rezavorli', 'ru' => 'Био курт ягодный', 'en' => 'Bio kurt, berry'], false, true],
            ['ck-4', 'classic', 4, null, ProductIllustration::KurtSingle, ['uz' => 'SAXIY qurt', 'ru' => 'Курт SAXIY', 'en' => 'SAXIY kurt'], true],
            ['ck-tosh', 'classic', 30, null, ProductIllustration::KurtStick, ['uz' => 'Tosh Qurt', 'ru' => 'Tosh Qurt', 'en' => 'Tosh Qurt']],
            ...collect($nuts)->map(fn (array $nut, string $key): array => ["nut-{$key}-30", 'nuts', 30, null, $nut[0], $nut[1], in_array($key, ['cashew', 'pista'], true)])->values(),
            ...collect(['cashew', 'pit', 'pista', 'almond'])->map(fn (string $key): array => ["nut-{$key}-60", 'nuts', 60, null, $nuts[$key][0], $nuts[$key][1]]),
            ...collect([40, 80, 160])->map(fn (int $weight): array => ["sul-{$weight}", 'suluguni', $weight, null, ProductIllustration::CheeseSticks, ['uz' => 'Suluguni', 'ru' => 'Сулугуни', 'en' => 'Suluguni'], $weight === 80]),
        ];

        foreach ($products as $index => $product) {
            [, $category, $weight, $box, $illustration, $name] = $product;

            Product::create([
                'category_id' => $categories[$category]->id,
                'description' => in_array($category, ['kurt', 'classic'], true) ? $kurtDescription : null,
                'name' => $name,
                'weight_grams' => $weight,
                'box_quantity' => $box,
                'illustration' => $illustration,
                'is_featured' => $product[6] ?? false,
                'is_new' => $product[7] ?? false,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}

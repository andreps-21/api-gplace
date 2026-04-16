<?php

namespace App\Actions\Maxdata;

use App\Models\Brand;
use App\Models\MeasurementUnit;
use App\Models\Product;
use App\Models\Section;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetProductMaxdata
{
    public function execute(
        string $url,
        string $token,
        string $empId,
        int $storeId,
        int $tenantId
    ) {
        $totalPages = 1;
        $page = 1;
        $products = [];
        do {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'empId' => $empId,
            ])
                ->withToken($token)
                ->get("{$url}/v1/ecommerce/produtocatalogo?page={$page}");

            if ($response->failed()) {
                Log::error("Erro na busca de produtos max data: " . json_encode($response->body()));
                throw new Exception($response->body());
            }

            $result = $response->json();
            $totalPages = $result['pages'];
            $page++;
            $products = array_merge($products, $result['docs']);
        } while ($page <= $totalPages);

        DB::beginTransaction();
        $sectionNames = array_column($products, 'grupo');
        $sectionsExists = Section::whereIn('name', $sectionNames)
            ->where('store_id', $storeId)
            ->get(['name'])
            ->pluck('name')
            ->all();

        $sectionsToInsert = array_unique(array_diff($sectionNames, $sectionsExists));

        $sectionsToInsert = array_map(function ($name) use ($storeId) {
            return [
                'store_id' => $storeId,
                'name' => $name,
                'descriptive' => null,
                'type' => 'S',
                'order' => 1,
                'use' => 1,
                'is_home' => false,
                'order_home' => 0,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $sectionsToInsert);

        Section::insert($sectionsToInsert);

        $sections = Section::where('store_id', $storeId)->get(['name', 'id']);

        $subGroupNames = array_column($products, 'subGrupo');

        $subGroupsExists = Section::whereIn('name', $subGroupNames)
            ->where('store_id', $storeId)
            ->where('type', 'A')
            ->get(['name'])
            ->pluck('name')
            ->all();

        $subGroupsToInsert = array_diff($subGroupNames, $subGroupsExists);

        $subGroupsToInsert = array_filter($products, function ($item) use ($subGroupsToInsert) {
            return in_array($item['subGrupo'], $subGroupsToInsert);
        });

        $subGroupsToInsert = array_map(function ($item) {
            return [
                'grupo' => $item["grupo"],
                'subGrupo' => $item["subGrupo"],
            ];
        }, $subGroupsToInsert);

        $subGroupsToInsert =  array_map("unserialize",  array_unique(array_map("serialize", $subGroupsToInsert)));

        $subGroupsToInsert = array_map(function ($item) use ($storeId, $sections) {
            return [
                'store_id' => $storeId,
                'name' => $item['subGrupo'],
                'descriptive' => null,
                'type' => 'A',
                'order' => 1,
                'use' => 1,
                'is_home' => false,
                'order_home' => 0,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'parent_id' => $sections->firstWhere('name', $item['grupo'])->id
            ];
        }, $subGroupsToInsert);

        Section::insert($subGroupsToInsert);

        Section::fixTree();

        $sections = Section::where('store_id', $storeId)->where('type', 'A')->get(['name', 'id']);

        $productCodes = array_column($products, 'proId');

        $productsExists = Product::whereIn('external_id', $productCodes)
            ->where('store_id', $storeId)
            ->get(['external_id'])
            ->pluck('external_id')
            ->all();

        $codesToInsert = array_diff($productCodes, $productsExists);

        $productCodes = array_filter($products, function ($item) use ($codesToInsert) {
            return in_array($item['proId'], $codesToInsert);
        });

        $measurements = MeasurementUnit::get(['id', 'initials']);

        $brandNames = array_column($products, 'fabricante');

        $bandsExists = Brand::whereIn('name', $brandNames)
            ->where('tenant_id', $tenantId)
            ->get(['name'])
            ->pluck('name')
            ->all();

        $namesToInsert = array_diff($brandNames, $bandsExists);

        $namesToInsert = collect($namesToInsert)->unique()
            ->map(function ($name) use ($tenantId) {
                return [
                    'name' => $name,
                    'is_enabled' => true,
                    'image' => null,
                    'is_public' => false,
                    'tenant_id' => $tenantId
                ];
            });

        Brand::insert($namesToInsert->all());

        $brands = Brand::query()
            ->where('tenant_id', $tenantId)
            ->get(['name', 'id']);

        $productsToInsert = array_map(function ($item) use ($sections, $measurements, $brands, $storeId) {
            return [
                'commercial_name' => $item['descricao'],
                'is_grid' => false,
                'reference' => uniqid(),
                'section_id' => $sections->firstWhere('name', $item['subGrupo'])->id,
                'origin' => 0,
                'type' => $item['tipo'],
                'um_id' => $measurements->firstWhere('initials', $item['un'])->id ?? $measurements->firstWhere('initials', 'UN')->id,
                'brand_id' => $brands->firstWhere('name', $item['fabricante'])->id,
                'price' => $item['valorVenda'],
                'promotion_price' => 0,
                'discount' => 0,
                'weight' => 0,
                'cubic_weight' => 0,
                'length' => 0,
                'width' => 0,
                'height' => 0,
                'is_enabled' => false,
                'quantity' => $item['estoque'],
                'external_id' => $item['proId'],
                'store_id' => $storeId,
                'sync_at' => now(),
            ];
        }, $productCodes);

        Product::insert($productsToInsert);

        $productCodes = array_column($products, 'proId');

        $insertedProducts = Product::query()
            ->whereIn('external_id', $productCodes)
            ->get(['id', 'external_id']);

        foreach ($insertedProducts->chunk(5000) as $chunk) {
            $cases = [];
            $ids = [];
            $quantities = [];

            foreach ($chunk as $product) {
                $productRequest = current(array_filter(
                    $products,
                    function ($item) use ($product) {
                        return $item['proId'] == $product->external_id;
                    }
                ));

                $cases[] = "WHEN {$product->id} then ?";
                $quantities[] = $productRequest['estoque'];
                $ids[] = $product->id;
            }
        }
        if (isset($ids) && isset($cases)) {
            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);

            if (!empty($ids)) {
                DB::update("UPDATE products SET `quantity` =  CASE `id` {$cases} END WHERE `id` in ({$ids})", $quantities);
            }
        }

        DB::commit();
        return $products;
    }
}

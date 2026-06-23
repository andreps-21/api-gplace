<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('code', 40)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('product_form_template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_form_template_id')->constrained('product_form_templates')->onDelete('cascade');
            $table->string('field_key', 80);
            $table->string('label', 120);
            $table->string('type', 30)->default('text');
            $table->boolean('is_fixed')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('show_on_ecommerce')->default(false);
            $table->boolean('show_as_filter')->default(false);
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_form_template_id', 'field_key'], 'pftf_template_field_unique');
        });

        Schema::create('store_product_field_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('product_form_template_id')->nullable()->constrained('product_form_templates')->nullOnDelete();
            $table->string('field_key', 80);
            $table->string('label', 120);
            $table->string('type', 30)->default('text');
            $table->boolean('is_fixed')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('show_on_ecommerce')->default(false);
            $table->boolean('show_as_filter')->default(false);
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'field_key'], 'spfs_store_field_unique');
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('store_product_field_setting_id')->constrained('store_product_field_settings')->onDelete('cascade');
            $table->string('field_key', 80);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'field_key'], 'pav_product_field_unique');
        });

        $this->seedTemplates();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('store_product_field_settings');
        Schema::dropIfExists('product_form_template_fields');
        Schema::dropIfExists('product_form_templates');
    }

    private function seedTemplates(): void
    {
        $now = now();
        $templates = [
            ['code' => 'generic', 'name' => 'Genérico', 'description' => 'Cadastro padrão para varejo geral.'],
            ['code' => 'fashion', 'name' => 'Moda', 'description' => 'Roupas, calçados e acessórios.'],
            ['code' => 'auto', 'name' => 'Auto', 'description' => 'Veículos, peças e acessórios automotivos.'],
            ['code' => 'supplements', 'name' => 'Suplementos/Cosméticos', 'description' => 'Suplementos, cosméticos e saúde.'],
            ['code' => 'electronics', 'name' => 'Eletrônicos', 'description' => 'Eletrônicos, informática e periféricos.'],
            ['code' => 'lifestyle', 'name' => 'Lifestyle', 'description' => 'Decoração, casa e itens de estilo de vida.'],
        ];

        foreach ($templates as $template) {
            $templateId = DB::table('product_form_templates')->insertGetId([
                ...$template,
                'is_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($this->fieldsFor($template['code']) as $field) {
                DB::table('product_form_template_fields')->insert([
                    'product_form_template_id' => $templateId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    ...$field,
                ]);
            }
        }
    }

    private function fixedField(string $key, string $label, int $order, bool $required = false): array
    {
        return [
            'field_key' => $key,
            'label' => $label,
            'type' => 'fixed',
            'is_fixed' => true,
            'is_visible' => true,
            'is_required' => $required,
            'show_on_ecommerce' => false,
            'show_as_filter' => false,
            'options' => null,
            'sort_order' => $order,
        ];
    }

    private function dynamicField(string $key, string $label, string $type, int $order, bool $ecommerce = true, bool $filter = false, ?array $options = null): array
    {
        return [
            'field_key' => $key,
            'label' => $label,
            'type' => $type,
            'is_fixed' => false,
            'is_visible' => true,
            'is_required' => false,
            'show_on_ecommerce' => $ecommerce,
            'show_as_filter' => $filter,
            'options' => $options ? json_encode($options) : null,
            'sort_order' => $order,
        ];
    }

    private function fieldsFor(string $code): array
    {
        $fixed = [
            $this->fixedField('images', 'Imagens', 10),
            $this->fixedField('reference', 'Referência', 20, true),
            $this->fixedField('sku', 'SKU', 30),
            $this->fixedField('commercial_name', 'Nome do produto', 40, true),
            $this->fixedField('type', 'Tipo', 50, true),
            $this->fixedField('fiscal', 'Dados fiscais', 60),
            $this->fixedField('category', 'Categoria', 70),
            $this->fixedField('brand', 'Marca', 80),
            $this->fixedField('measurement_unit', 'Unidade de medida', 90, true),
            $this->fixedField('price_stock', 'Preços e estoque', 100),
            $this->fixedField('payment_methods', 'Métodos de pagamento', 110),
            $this->fixedField('description', 'Descrição', 120),
        ];

        $dynamic = match ($code) {
            'fashion' => [
                $this->dynamicField('size', 'Tamanho', 'select', 200, true, true, ['PP', 'P', 'M', 'G', 'GG']),
                $this->dynamicField('color', 'Cor', 'color', 210, true, true),
                $this->dynamicField('material', 'Material', 'text', 220),
                $this->dynamicField('gender', 'Gênero', 'select', 230, true, true, ['Feminino', 'Masculino', 'Unissex', 'Infantil']),
                $this->dynamicField('collection', 'Coleção', 'text', 240),
            ],
            'auto' => [
                $this->dynamicField('year', 'Ano', 'number', 200, true, true),
                $this->dynamicField('model', 'Modelo', 'text', 210, true, true),
                $this->dynamicField('mileage', 'Quilometragem', 'number', 220, true, true),
                $this->dynamicField('fuel', 'Combustível', 'select', 230, true, true, ['Flex', 'Gasolina', 'Diesel', 'Elétrico', 'Híbrido']),
                $this->dynamicField('transmission', 'Câmbio', 'select', 240, true, true, ['Manual', 'Automático']),
                $this->dynamicField('doors', 'Portas', 'number', 250, true, true),
            ],
            'supplements' => [
                $this->dynamicField('formula', 'Fórmula', 'text', 200),
                $this->dynamicField('dosage', 'Dosagem', 'text', 210),
                $this->dynamicField('usage_mode', 'Modo de uso', 'text', 220),
                $this->dynamicField('benefits', 'Benefícios', 'text', 230),
                $this->dynamicField('contraindication', 'Contraindicação', 'text', 240),
            ],
            'electronics' => [
                $this->dynamicField('model', 'Modelo', 'text', 200, true, true),
                $this->dynamicField('warranty', 'Garantia', 'text', 210, true),
                $this->dynamicField('voltage', 'Voltagem', 'select', 220, true, true, ['110V', '220V', 'Bivolt']),
                $this->dynamicField('specifications', 'Especificações', 'text', 230, true),
            ],
            'lifestyle' => [
                $this->dynamicField('material', 'Material', 'text', 200, true, true),
                $this->dynamicField('style', 'Estilo', 'text', 210, true, true),
                $this->dynamicField('room', 'Ambiente', 'select', 220, true, true, ['Sala', 'Quarto', 'Cozinha', 'Escritório', 'Área externa']),
                $this->dynamicField('dimensions', 'Medidas', 'text', 230, true),
            ],
            default => [],
        };

        return [...$fixed, ...$dynamic];
    }
};

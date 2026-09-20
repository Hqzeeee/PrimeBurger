<?php
class ProductController
{
    private ProductModel $products;

    public function __construct()
    {
        $this->products = new ProductModel();
    }

    /** @return array{0: bool, 1: string, 2: array} [success, message, fieldErrors] */
    public function store(array $post): array
    {
        Csrf::verifyRequest();
        $data = Validator::clean($post);

        $v = new Validator($data);
        $v->required('name', 'Product name')->maxLength('name', 150, 'Product name')
          ->required('unit', 'Unit')
          ->required('quantity', 'Quantity')->numeric('quantity', 'Quantity')->min('quantity', 0, 'Quantity')
          ->required('minimum_stock_level', 'Minimum stock level')->numeric('minimum_stock_level', 'Minimum stock level')->min('minimum_stock_level', 0, 'Minimum stock level')
          ->required('cost_price', 'Cost price')->numeric('cost_price', 'Cost price')->min('cost_price', 0, 'Cost price')
          ->numeric('selling_price', 'Selling price');

        if ($v->fails()) {
            return [false, 'Please correct the errors below.', $v->errors()];
        }

        $data['product_code'] = $this->products->generateProductCode();
        $data['qr_code'] = $this->products->generateQrCode($data['product_code']);
        $data['selling_price'] = $data['selling_price'] ?: 0;

        $id = $this->products->create($data, Auth::id());
        ActivityLogger::log('product_created', "Added product {$data['name']} ({$data['product_code']})");

        return [true, "Product \"{$data['name']}\" was added with code {$data['product_code']}.", []];
    }

    public function update(int $id, array $post): array
    {
        Csrf::verifyRequest();
        $data = Validator::clean($post);

        $v = new Validator($data);
        $v->required('name', 'Product name')->maxLength('name', 150, 'Product name')
          ->required('unit', 'Unit')
          ->required('minimum_stock_level', 'Minimum stock level')->numeric('minimum_stock_level', 'Minimum stock level')->min('minimum_stock_level', 0, 'Minimum stock level')
          ->required('cost_price', 'Cost price')->numeric('cost_price', 'Cost price')->min('cost_price', 0, 'Cost price')
          ->numeric('selling_price', 'Selling price');

        if ($v->fails()) {
            return [false, 'Please correct the errors below.', $v->errors()];
        }

        $data['selling_price'] = $data['selling_price'] ?: 0;
        $this->products->update($id, $data);
        ActivityLogger::log('product_updated', "Updated product #{$id}");

        return [true, 'Product updated successfully.', []];
    }

    public function archive(int $id): void
    {
        Csrf::verifyRequest();
        $this->products->archive($id);
        ActivityLogger::log('product_archived', "Archived product #{$id}");
    }
}

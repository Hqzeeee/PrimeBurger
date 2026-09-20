<?php
class StockController
{
    private InventoryTransactionModel $transactions;

    public function __construct()
    {
        $this->transactions = new InventoryTransactionModel();
    }

    /** @return array{0: bool, 1: string} [success, message] */
    public function record(array $post): array
    {
        Csrf::verifyRequest();
        $data = Validator::clean($post);

        $v = new Validator($data);
        $v->required('product_id', 'Product')->numeric('product_id', 'Product')
          ->required('type', 'Transaction type')
          ->required('quantity', 'Quantity')->numeric('quantity', 'Quantity')->min('quantity', 0.01, 'Quantity')
          ->required('transaction_date', 'Date');

        if (!in_array($data['type'] ?? '', ['in', 'out'], true)) {
            return [false, 'Invalid transaction type.'];
        }
        if ($v->fails()) {
            return [false, 'Please fill in all required fields with valid values.'];
        }

        try {
            $this->transactions->record(
                (int)$data['product_id'],
                $data['type'],
                (float)$data['quantity'],
                $data['reason'] ?? '',
                $data['transaction_date'],
                Auth::id()
            );
        } catch (InvalidArgumentException $e) {
            return [false, $e->getMessage()];
        }

        $label = $data['type'] === 'in' ? 'Stock-in' : 'Stock-out';
        ActivityLogger::log('stock_' . $data['type'], "{$label} of {$data['quantity']} recorded for product #{$data['product_id']}");

        return [true, "{$label} recorded successfully."];
    }
}

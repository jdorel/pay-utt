<?php

declare(strict_types=1);

namespace App\GraphQL\Interfaces;

use App\Models\AuthorisationTransaction;
use App\Models\ImmediateTransaction;
use App\Models\RefundTransaction;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InterfaceType;

class Transaction extends InterfaceType
{
    protected $attributes = [
        'name' => 'Transaction',
        'description' => 'Implement transaction squelette'
    ];

    public function resolveType($root)
    {
        switch (true)
        {
            case $root instanceof ImmediateTransaction:
                return GraphQL::type('ImmediateTransaction');
                break;
            case $root instanceof AuthorisationTransaction:
                return GraphQL::type('AutorisationTransaction');
                break;
            case $root instanceof RefundTransaction:
                return GraphQL::type('RefundTransaction');
                break;
        }
        return response()->json($root);
    }

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Transaction ID',
            ],
            'uuid' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Transaction UUID'
            ],
            'parent' => [
                'type' => GraphQL::type('Transaction'),
                'description' => 'Parent transaction (if exist)',
                'always' => ['type', 'id', 'amount'],
            ],
            'childrens' => [
                'type' => Type::listOf(GraphQL::type('Transaction')),
                'description' => 'Children transaction (if exist)',
                'always' => ['type', 'id', 'amount'],
            ],
            'service' => [
                'type' => GraphQL::type('Service'),
                'description' => 'Service transaction owner',
            ],
            'amount' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Transaction amount (centimes ???)'
            ],
            'solde' => [
                'type' => Type::int(),
                'description' => 'Total amount of the whole transaction (payment - refund)',
                'selectable' => false
            ],
            'lastname' => [
                'type' => Type::string(),
                'description' => 'Client lastname'
            ],
            'firstname' => [
                'type' => Type::string(),
                'description' => 'Client firstname'
            ],
            'mail' => [
                'type' => Type::string(),
                'description' => 'Client mail',
                'alias' => 'client_mail'
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Transaction description'
            ],
            'service_data' => [
                'type' => Type::string(),
                'description' => 'Tag from service request'
            ],
            'articles' => [
                'type' => Type::listOf(GraphQL::type('Article')),
                'description' => 'Transaction cart',
                'is_relation' => false
            ],
            'report' => [
                'type' => GraphQL::type('Report'),
                'description' => 'Transaction cart',
            ],
            'step' => [
                'type' => GraphQL::type('Step'),
                'description' => 'Current step of the transaction',
            ],
            'type' => [
                'type' => GraphQL::type('TransactionType'),
                'description' => 'Type of the transaction',
            ],
            'bank_explaination' => [
                'type' => Type::string(),
                'description' => 'Humanised bank report',
                'selectable' => false,
            ],
            'created_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Creation date of the transaction'
            ],
            'updated_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Update date of the transaction'
            ],
            'is_real' => [
                'type' => Type::boolean(),
                'description' => 'Is the transaction real.',
                'selectable' => false
            ]

        ];
    }
    protected function resolveCreatedAtField($root, $args)
    {
        return (string) $root->created_at;
    }

    protected function resolveUpdatedAtField($root, $args)
    {
        return (string) $root->updated_at;
    }

    protected function resolveIsRealField($root, $args)
    {
        return (bool) $root->provider != 'Dev';
    }

    protected function resolveBankExplainationField($root, $args)
    {
        $msg = null;
        if($root->getProvider())
        try {
            $msg = $root->getProvider()->getHumanisedReport($root);
        } catch (\Exception $exception){
            $msg = null;
        };

        return (string) $msg;
    }
}

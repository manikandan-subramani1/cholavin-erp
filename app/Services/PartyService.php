<?php

namespace App\Services;

use App\Models\Party;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyService
{
    public function metrics(string $type): array
    {
        $rows = $this->filteredQuery($type, new Request)->get();
        [$positiveTypes, , $paymentType] = $this->balanceTypes($type);
        $partyIds = $rows->modelKeys();
        $outstanding = $rows->sum(function (Party $party) use ($type) {
            $opening = (float) $party->opening_balance * ($party->balance_type === ($type === 'customer' ? 'receivable' : 'payable') ? 1 : -1);

            return $opening + (float) $party->positive_balance - (float) $party->negative_balance - (float) $party->unallocated_payments;
        });

        $documents = DB::table('commercial_documents')
            ->whereIn('party_id', $partyIds ?: [0])
            ->where('status', 'posted')
            ->whereIn('type', $positiveTypes);
        $payments = DB::table('payments')
            ->whereIn('party_id', $partyIds ?: [0])
            ->where('type', $paymentType);

        return [
            'total' => $rows->count(),
            'active' => $rows->where('is_active', true)->count(),
            'outstanding' => round((float) $outstanding, 2),
            'overdue' => round((float) (clone $documents)->whereDate('due_date', '<', today())->where('balance_amount', '>', 0)->sum('balance_amount'), 2),
            'business_month' => round((float) (clone $documents)->whereBetween('document_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('total_amount'), 2),
            'payments_month' => round((float) (clone $payments)->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount'), 2),
        ];
    }

    public function filteredQuery(string $type, Request $request): Builder
    {
        $search = is_array($request->input('search')) ? data_get($request->input('search'), 'value') : $request->input('search');
        [$positiveTypes, $negativeTypes, $paymentType] = $this->balanceTypes($type);

        return Party::query()
            ->select('parties.*')
            ->selectSub(
                DB::table('commercial_documents')
                    ->selectRaw('COALESCE(SUM(balance_amount), 0)')
                    ->whereColumn('party_id', 'parties.id')
                    ->where('status', 'posted')
                    ->whereIn('type', $positiveTypes),
                'positive_balance'
            )
            ->selectSub(
                DB::table('commercial_documents')
                    ->selectRaw('COALESCE(SUM(balance_amount), 0)')
                    ->whereColumn('party_id', 'parties.id')
                    ->where('status', 'posted')
                    ->whereIn('type', $negativeTypes),
                'negative_balance'
            )
            ->selectSub(
                DB::table('payments')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('party_id', 'parties.id')
                    ->whereNull('commercial_document_id')
                    ->where('type', $paymentType),
                'unallocated_payments'
            )
            ->with(['group:id,name', 'addresses' => fn ($query) => $query->where('is_default', true)])
            ->forActiveShop()
            ->whereIn('type', [$type, 'both'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->when($request->filled('group_id'), fn ($query) => $query->where('group_id', $request->integer('group_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to_date')));
    }

    public function profile(Party $party, string $type): array
    {
        [$positiveTypes, $negativeTypes, $paymentType] = $this->balanceTypes($type);
        $positive = (float) $party->documents()->where('status', 'posted')->whereIn('type', $positiveTypes)->sum('balance_amount');
        $negative = (float) $party->documents()->where('status', 'posted')->whereIn('type', $negativeTypes)->sum('balance_amount');
        $unallocated = (float) $party->payments()->whereNull('commercial_document_id')->where('type', $paymentType)->sum('amount');
        $opening = (float) $party->opening_balance * ($party->balance_type === ($type === 'customer' ? 'receivable' : 'payable') ? 1 : -1);

        return [
            'party' => $party->load(['group:id,name', 'addresses']),
            'summary' => [
                'outstanding' => round($opening + $positive - $negative - $unallocated, 2),
                'total_business' => (float) $party->documents()->where('status', 'posted')->whereIn('type', $positiveTypes)->sum('total_amount'),
                'payments' => (float) $party->payments()->where('type', $paymentType)->sum('amount'),
                'transactions' => $party->documents()->count() + $party->payments()->count(),
                'credit_available' => max(0, (float) $party->credit_limit - max(0, $opening + $positive - $negative - $unallocated)),
            ],
        ];
    }

    public function transactionQuery(Party $party, Request $request): QueryBuilder
    {
        $documents = DB::table('commercial_documents as documents')
            ->where('documents.party_id', $party->id)
            ->where('documents.shop_id', $party->shop_id)
            ->selectRaw("'document' as record_kind, documents.id as source_id, documents.type as source_type, documents.number, documents.document_date as transaction_date, documents.total_amount as total, documents.balance_amount as balance, documents.due_date, documents.status");

        $payments = DB::table('payments')
            ->where('payments.party_id', $party->id)
            ->where('payments.shop_id', $party->shop_id)
            ->selectRaw("'payment' as record_kind, payments.id as source_id, payments.type as source_type, payments.number, payments.payment_date as transaction_date, payments.amount as total, 0 as balance, NULL as due_date, 'posted' as status");

        $search = is_array($request->input('search')) ? data_get($request->input('search'), 'value') : $request->input('search');

        return DB::query()->fromSub($documents->unionAll($payments), 'party_transactions')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to_date')))
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('number', 'like', "%{$search}%")->orWhere('source_type', 'like', "%{$search}%")));
    }

    public function create(array $data, string $type): Party
    {
        return DB::transaction(function () use ($data, $type) {
            $party = Party::create($this->payload($data, $type));
            $this->syncAddress($party, $data);

            return $party->load('addresses');
        });
    }

    public function update(Party $party, array $data, string $type): Party
    {
        return DB::transaction(function () use ($party, $data, $type) {
            $party->update($this->payload($data, $type));
            $this->syncAddress($party, $data);

            return $party->refresh()->load('addresses');
        });
    }

    private function payload(array $data, string $type): array
    {
        return collect($data)->except(['address', 'city', 'state', 'postal_code'])->merge([
            'shop_id' => session('active_shop_id'),
            'type' => $type,
            'code' => strtoupper($data['code']),
            'is_active' => (bool) $data['is_active'],
        ])->all();
    }

    private function syncAddress(Party $party, array $data): void
    {
        if (! filled($data['address'] ?? null)) {
            return;
        }

        $party->addresses()->updateOrCreate(['type' => 'billing', 'is_default' => true], [
            'address' => $data['address'],
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
        ]);
    }

    private function balanceTypes(string $type): array
    {
        return $type === 'customer'
            ? [['sales_invoice', 'pos_invoice'], ['sales_return', 'credit_note'], 'customer_collection']
            : [['purchase_bill'], ['purchase_return', 'debit_note'], 'supplier_payment'];
    }
}

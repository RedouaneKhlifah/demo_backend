<?php
namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    public function syncPayments(int $factureId, array $payments): void
    {
        try {
            DB::transaction(function () use ($factureId, $payments) {
                if (empty($payments)) {
                    Payment::where('facture_id', $factureId)->delete();
                    return;
                }

                // Use raw queries for maximum performance on large datasets
                $existingIds = DB::table('payments')
                    ->where('facture_id', $factureId)
                    ->pluck('id')
                    ->toArray();

                $incomingIds = collect($payments)->pluck('id')->filter()->toArray();
                $toDelete = array_diff($existingIds, $incomingIds);

                // Chunked delete for large datasets
                if (!empty($toDelete)) {
                    foreach (array_chunk($toDelete, 1000) as $chunk) {
                        DB::table('payments')->whereIn('id', $chunk)->delete();
                    }
                }

                // Prepare data for upsert
                $upsertData = [];
                $now = now();

                foreach ($payments as $payment) {
                    // Validate payment date before processing
                    try {
                        $paymentDate = Carbon::parse($payment['payment_date'])->toDateString();
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException(
                            "Invalid payment date format for payment: " . json_encode($payment)
                        );
                    }

                    $upsertData[] = [
                        'id' => $payment['id'] ?? null,
                        'facture_id' => $factureId,
                        'amount' => $payment['amount'],
                        'type' => $payment['type'],
                        'payment_date' => $paymentDate,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Remove null IDs for new records, keep them for updates
                $toInsert = array_filter($upsertData, fn($item) => is_null($item['id']));
                $toUpdate = array_filter($upsertData, fn($item) => !is_null($item['id']));

                // Process in chunks for large datasets
                if (!empty($toInsert)) {
                    foreach (array_chunk($toInsert, 1000) as $chunk) {
                        // Remove id field for inserts
                        $insertChunk = array_map(
                            fn($item) => array_diff_key($item, ['id' => null]), 
                            $chunk
                        );
                        DB::table('payments')->insert($insertChunk);
                    }
                }

                if (!empty($toUpdate)) {
                    foreach (array_chunk($toUpdate, 500) as $chunk) {
                        Payment::upsert(
                            $chunk,
                            ['id'],
                            ['amount', 'type', 'payment_date', 'updated_at']
                        );
                    }
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Log database-specific errors
            Log::error('Database error during payment sync', [
                'facture_id' => $factureId,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            
            throw new \RuntimeException(
                "Failed to sync payments for facture {$factureId}: Database error", 
                0, 
                $e
            );
        } catch (\InvalidArgumentException $e) {
            // Log validation errors
            Log::error('Invalid payment data during sync', [
                'facture_id' => $factureId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e; // Re-throw validation errors as-is
        } catch (\Exception $e) {
            // Log any other unexpected errors
            Log::error('Unexpected error during payment sync', [
                'facture_id' => $factureId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \RuntimeException(
                "Failed to sync payments for facture {$factureId}: {$e->getMessage()}", 
                0, 
                $e
            );
        }
    }
}
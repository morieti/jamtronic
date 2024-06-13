<?php

namespace App\Jobs;

use App\Models\ShippingMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class UpdateShippingMethods implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            // Deactivate expired active shipping methods
            ShippingMethod::query()
                ->where('type', 'express_option')
                ->where('status', 'active')
                ->where('ends_at', '<', now())
                ->update(['status' => 'inactive']);

            $express = ShippingMethod::query()->where('type', 'express')->first();

            // Create new active shipping methods
            for ($i = 1; $i <= 7; $i++) {
                $day = now()->addDays($i);
                $dayName = $day->dayName;
                $jDay = Jalalian::fromCarbon($day);

                $methodId = $this->createMethod([
                    'title' => sprintf("%s %s/%s", $dayName, str_pad($jDay->getMonth(), 2, '0', STR_PAD_LEFT), $jDay->getDay()),
                    'parent_id' => $express->id,
                    'starts_at' => $day->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                    'ends_at' => $day->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
                ]);

                $this->createMethod([
                    'title' => 'ساعت ۹ تا ۱۴',
                    'capacity' => 10,
                    'parent_id' => $methodId,
                    'starts_at' => $day->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
                    'ends_at' => $day->setTime(13, 0, 0)->format('Y-m-d H:i:s'),
                ]);

                $this->createMethod([
                    'title' => 'ساعت ۱۴ تا ۲۱',
                    'capacity' => 10,
                    'parent_id' => $methodId,
                    'starts_at' => $day->setTime(14, 0, 0)->format('Y-m-d H:i:s'),
                    'ends_at' => $day->setTime(20, 0, 0)->format('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Exception $exception) {
            Log::error('UpdateShippingMethods Job Failed', [
                'message' => $exception->getMessage(),
                'stack' => $exception->getTraceAsString(),
            ]);
        }
    }

    public function createMethod($data): int
    {
        try {
            $method = ShippingMethod::create([
                'type' => 'express_option',
                'title' => $data['title'],
                'subtitle' => null,
                'price_caption' => null,
                'parent_id' => $data['parent_id'],
                'status' => 'active',
                'capacity' => $data['capacity'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
            ]);
        } catch (QueryException $exception) {
            $method = ShippingMethod::query()
                ->where('parent_id', $data['parent_id'])
                ->where('type', 'express_option')
                ->where('status', 'active')
                ->where('starts_at', $data['starts_at'])
                ->where('ends_at', $data['ends_at'])
                ->first();
        } catch (\Throwable $exception) {
            Log::error('Error creating shipping method', [
                'message' => $exception->getMessage(),
                'stack' => $exception->getTraceAsString(),
            ]);
            return 0;
        }

        return $method->id;
    }
}

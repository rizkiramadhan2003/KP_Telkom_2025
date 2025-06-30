<?php

namespace App\Http\Controllers;

use App\Models\FalloutReport;
use App\Models\FalloutStatus;
use App\Models\HdDaman;
use App\Models\OrderType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    public function handle()
    {
        Log::info('Webhook received!');
        $update = Telegram::getWebhookUpdate();
        Log::info($update);

        if (!$update->has('message')) {
            Log::warning('Update does not contain a message.');
            return;
        }

        $chat_id = $update->getChat()->getId();
        $text = $update->getMessage()->getText();
        $user = $update->getMessage()->getFrom();

        $conversationState = Cache::get($chat_id, ['step' => 'idle']);

        if ($text === '/start' || $text === '/newreport') {
            $this->startNewReport($chat_id, $user->getFirstName());
        } else {
            $this->continueConversation($chat_id, $text, $conversationState);
        }
    }

    private function startNewReport($chat_id, $userName)
    {
        $this->sendMessage($chat_id, "Halo {$userName}! Selamat datang di sistem pelaporan fallout. Mari kita mulai.");

        $today = Carbon::today()->format('Y-m-d');

        // Get or create daily counter
        $counter = DB::table('daily_counters')->where('report_date', $today)->first();

        if (!$counter) {
            // If no counter for today, reset to 1
            DB::table('daily_counters')->insert([
                'report_date' => $today,
                'last_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $currentNumber = 1;
        } else {
            // Increment existing counter
            DB::table('daily_counters')->where('report_date', $today)->increment('last_number');
            $currentNumber = $counter->last_number + 1;
        }

        $reportNumber = 'FR-' . Carbon::now()->format('Ymd') . '-' . str_pad($currentNumber, 3, '0', STR_PAD_LEFT);

        $state = [
            'step' => 'hd_daman',
            'report_data' => [
                'no_report' => $reportNumber,
                'reporter_name' => $userName,
                'tanggal' => $today,
            ],
        ];

        Cache::put($chat_id, $state, now()->addMinutes(30));
        $this->sendMessage($chat_id, "No Laporan Anda: *{$currentNumber}*\n\n");
        $this->askForHdDaman($chat_id);
    }

    private function continueConversation($chat_id, $text, & $state)
    {
        $step = $state['step'];

        switch ($step) {
            case 'hd_daman':
                $this->handleHdDaman($chat_id, $text, $state);
                break;

            case 'order_type':
                $this->handleOrderType($chat_id, $text, $state);
                break;

            case 'order_id':
                $state['report_data']['order_id'] = $text;
                $state['step'] = 'fallout_status';
                $this->askForFalloutStatus($chat_id);
                break;

            case 'fallout_status':
                $this->handleFalloutStatus($chat_id, $text, $state);
                break;

            case 'keterangan':
                $state['report_data']['keterangan'] = $text;
                $this->saveReport($chat_id, $state);
                break;

            default:
                $this->sendMessage($chat_id, "Gunakan perintah /newreport untuk memulai laporan baru.");
                break;
        }

        if ($state['step'] !== 'idle') {
            Cache::put($chat_id, $state, now()->addMinutes(30));
        }
    }

    private function askForHdDaman($chat_id)
    {
        $damans = HdDaman::all()->pluck('name')->toArray();
        $message = "Pilih HD Daman:\n" . implode("\n", $damans);
        $this->sendMessage($chat_id, $message);
    }

    private function handleHdDaman($chat_id, $text, & $state)
    {
        $daman = HdDaman::where('name', 'like', "%{$text}%")->first();
        if ($daman) {
            $state['report_data']['hd_daman_id'] = $daman->id;
            $state['step'] = 'order_type';
            $this->askForOrderType($chat_id);
        } else {
            $this->sendMessage($chat_id, "HD Daman tidak ditemukan. Silakan coba lagi.");
            $this->askForHdDaman($chat_id);
        }
    }

    private function askForOrderType($chat_id)
    {
        $types = OrderType::all()->pluck('name')->toArray();
        $message = "Pilih Tipe Order:\n" . implode("\n", $types);
        $this->sendMessage($chat_id, $message);
    }

    private function handleOrderType($chat_id, $text, & $state)
    {
        $type = OrderType::where('name', 'like', "%{$text}%")->first();
        if ($type) {
            $state['report_data']['order_type_id'] = $type->id;
            $state['step'] = 'order_id';
            $this->sendMessage($chat_id, "Masukkan Order ID:");
        } else {
            $this->sendMessage($chat_id, "Tipe Order tidak ditemukan. Silakan coba lagi.");
            $this->askForOrderType($chat_id);
        }
    }

    private function askForFalloutStatus($chat_id)
    {
        $statuses = FalloutStatus::all()->pluck('name')->toArray();
        $message = "Pilih Status Fallout:\n" . implode("\n", $statuses);
        $this->sendMessage($chat_id, $message);
    }

    private function handleFalloutStatus($chat_id, $text, & $state)
    {
        $status = FalloutStatus::where('name', 'like', "%{$text}%")->first();
        if ($status) {
            $state['report_data']['fallout_status_id'] = $status->id;
            $state['step'] = 'keterangan';
            $this->sendMessage($chat_id, "Masukkan Keterangan (opsional):");
        } else {
            $this->sendMessage($chat_id, "Status Fallout tidak ditemukan. Silakan coba lagi.");
            $this->askForFalloutStatus($chat_id);
        }
    }

    private function saveReport($chat_id, $state)
    {
        FalloutReport::create($state['report_data']);
        $this->sendMessage($chat_id, "Laporan berhasil disimpan. Terima kasih!");
        Cache::forget($chat_id);
        $state['step'] = 'idle';
    }

    private function sendMessage($chat_id, $message)
    {
        Telegram::sendMessage([
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }
}
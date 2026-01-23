<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\WelcomeMessage;
use App\Services\WelcomeMessageService;
use Illuminate\Console\Command;

final class WelcomeMessageManager extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'welcome:message {action=list} {--module=} {--message=} {--type=personal}';

    /**
     * The console command description.
     */
    protected $description = 'Manage welcome messages for different modules';

    public function __construct(
        private WelcomeMessageService $welcomeMessageService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listMessages(),
            'set' => $this->setMessage(),
            'delete' => $this->deleteMessage(),
            'clear-cache' => $this->clearCache(),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    private function listMessages(): int
    {
        $messages = WelcomeMessage::orderBy('module')->get();

        if ($messages->isEmpty()) {
            $this->info('No welcome messages found.');
            return Command::SUCCESS;
        }

        $this->info('Welcome Messages:');
        $this->table(
            ['ID', 'Module', 'Type', 'Message', 'Active'],
            $messages->map(fn($msg) => [
                $msg->id,
                $msg->module,
                $msg->type,
                $msg->message,
                $msg->is_active ? 'Yes' : 'No',
            ])
        );

        return Command::SUCCESS;
    }

    private function setMessage(): int
    {
        $module = $this->option('module') ?: $this->ask('Module name');
        $message = $this->option('message') ?: $this->ask('Welcome message');
        $type = $this->option('type');

        if (!$module || !$message) {
            $this->error('Module and message are required.');
            return Command::FAILURE;
        }

        try {
            WelcomeMessage::updateOrCreate(
                ['module' => $module, 'type' => $type],
                [
                    'message' => $message,
                    'is_active' => true,
                ]
            );

            $this->welcomeMessageService->clearCache($module);

            $this->info("Welcome message for '{$module}' ({$type}) has been set successfully.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to set welcome message: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function deleteMessage(): int
    {
        $module = $this->option('module') ?: $this->ask('Module name to delete');

        if (!$module) {
            $this->error('Module name is required.');
            return Command::FAILURE;
        }

        try {
            $deleted = WelcomeMessage::where('module', $module)->delete();

            if ($deleted) {
                $this->welcomeMessageService->clearCache($module);
                $this->info("Welcome message(s) for '{$module}' have been deleted.");
            } else {
                $this->warn("No welcome message found for '{$module}'.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to delete welcome message: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function clearCache(): int
    {
        try {
            $this->welcomeMessageService->clearCache();
            $this->info('All welcome message caches have been cleared.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clear cache: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}




<?php

namespace App\Console\Commands;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectDispute;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResolveDisputes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispute:resolve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredDisputes = ProjectDispute::with('project')
        ->where('status', DisputeStatus::Open)
        ->where('expired_at', '<', now())
        ->get();

        if ($expiredDisputes->isEmpty()) {
            $this->info('No expired disputes available');
            \Log::info('No expired disputes available');
            return;
        }

        try {
            \DB::beginTransaction();
            foreach($expiredDisputes as $dispute){
                $dispute->update(['status' => DisputeStatus::Expired]);
                $dispute->project->update(['status' => ProjectStatus::Canceled]);
                \DB::commit();
                \Log::info('Expired disputes: ' . $dispute->project_id . ' date: '. $dispute->expired_at);
            }
        }catch(\Exception $e){
            \DB::rollBack();
            \Log::error('Error trying to cancel project: '.$e->getMessage());
        }

    }
}

<?php

namespace App\Console\Commands;

use App\Models\DoctorSession;
use Illuminate\Console\Command;

class ExpireSessions extends Command
{
    protected $signature   = 'sessions:expire';
    protected $description = 'Mark all expired doctor sessions as expired status';

    public function handle(): int
    {
        $count = DoctorSession::where('expires_at', '<=', now())
                              ->whereIn('status', ['pending', 'active'])
                              ->update(['status' => 'expired']);

        $this->info("✅  {$count} doctor session(s) marked as expired.");
        return 0;
    }
}

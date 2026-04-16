<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class InactivateCouponDefeated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:inactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Desativa cupons vencidos.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $quantity =  Coupon::where('is_enabled', true)
            ->where('end_at', '<', now())
            ->update(['is_enabled' => false]);

        $this->info("{$quantity} cupons desativados");

        return 0;
    }
}

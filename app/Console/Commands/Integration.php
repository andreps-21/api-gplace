<?php

namespace App\Console\Commands;

use App\Actions\IntegrationAction;
use App\Models\Setting;
use Illuminate\Console\Command;

class Integration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza integração com a maxdata';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $settings = Setting::whereNotNull('integration_info')
            ->get(['store_id', 'integration_info']);

        $action = new IntegrationAction();
        foreach ($settings as  $element) {
            if (isset($element->integration_info['url'])) {
                $action->execute($element['store_id']);
            }
        }

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PromotionManager;

class ExpirePromotions extends Command
{
    protected $signature = 'promotions:expire';
    protected $description = 'Expire old promotions that have passed their end date';

    protected $promotionManager;

    public function __construct(PromotionManager $promotionManager)
    {
        parent::__construct();
        $this->promotionManager = $promotionManager;
    }

    public function handle()
    {
        $this->info('Checking for expired promotions...');
        
        $expired = $this->promotionManager->expireOldPromotions();
        
        if ($expired > 0) {
            $this->info("Expired {$expired} promotion(s)");
        } else {
            $this->info('No promotions to expire');
        }
        
        return 0;
    }
}

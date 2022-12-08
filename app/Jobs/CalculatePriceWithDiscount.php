<?php

namespace App\Jobs;

use App\Jobs\Middleware\AmoTokenExpirationControl;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculatePriceWithDiscount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $lead;
    private $oldPrice;
    private $oldStatus;
    private $oldCard;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Lead $lead, $oldPrice = 0, $oldStatus = null, $oldCard = null)
    {
        $this->lead      = $lead;
        $this->oldPrice  = $oldPrice;
        $this->oldStatus = $oldStatus;
        $this->oldCard   = $oldCard;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->lead->calculateDiscountPrice(
            $this->oldPrice,
            $this->oldStatus,
            $this->oldCard
        );
    }

    /**
     * Get the intermediary through which the job should go.
     *
     * @return array
     */
    public function middleware()
    {
        return [new AmoTokenExpirationControl];
    }
}

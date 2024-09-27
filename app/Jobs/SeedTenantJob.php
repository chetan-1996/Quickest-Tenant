<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\{
    Tenant,
    User
};

class SeedTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $tenant;
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->tenant->run(function (){
            $start_date = date('Y-m-d H:i:s');
            $from_date = date('Y-m-d H:i:s', strtotime("+7 day", strtotime($start_date)));
            $user = User::query()->create([
                'name'=>$this->tenant->name,
                'email'=>$this->tenant->email,
                'password'=>$this->tenant->password,
                'mobile_no'=>$this->tenant->mobile_no,
                'country_id'=>$this->tenant->country_id,
                'state_id'=>$this->tenant->state_id,
                'company_category'=>$this->tenant->company_category,
                'domain'=>$this->tenant->domain,
                'company_id'=>1,
                'status' => 'Approved',
                'invite_status' => 1,
                'plan_start_date' => $start_date,
                'plan_end_date' => $from_date,
            ]);
        });
        
    }
}

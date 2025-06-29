<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixVendorDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:vendor-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix vendor dashboard issues for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Activate all vendor memberships for testing
        $vendors = User::where('role', 'vendor')->get();
        
        foreach ($vendors as $vendor) {
            $vendor->update([
                'membership_active' => true,
                'membership_paid_at' => now()
            ]);
        }
        
        $this->info("Activated membership for {$vendors->count()} vendors.");
        
        // Fix the controller file
        $controllerPath = app_path('Http/Controllers/Vendor/VendorDashboardController.php');
        $content = file_get_contents($controllerPath);
        
        // Replace the return type
        $content = str_replace(
            'public function index(): View',
            'public function index(): View|RedirectResponse',
            $content
        );
        
        // Add import if not exists
        if (!str_contains($content, 'use Illuminate\Http\RedirectResponse;')) {
            $content = str_replace(
                'use Illuminate\View\View;',
                "use Illuminate\View\View;\nuse Illuminate\Http\RedirectResponse;",
                $content
            );
        }
        
        file_put_contents($controllerPath, $content);
        
        $this->info('Fixed VendorDashboardController return type.');
        $this->info('You can now access the vendor dashboard!');
        
        return 0;
    }
}

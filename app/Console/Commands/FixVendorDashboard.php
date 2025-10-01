<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Class FixVendorDashboard
 *
 * A console command designed for testing and development environments to quickly resolve
 * common issues preventing access to the vendor dashboard.
 *
 * @package App\Console\Commands
 */
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
    protected $description = 'Fix vendor dashboard issues for testing by activating memberships and patching controller return types.';

    /**
     * Execute the console command.
     *
     * This command performs two main actions for testing purposes:
     * 1. It activates the membership for all users with the 'vendor' role.
     * 2. It patches the `VendorDashboardController` to include `RedirectResponse` in the `index`
     *    method's return type hint, preventing potential type errors during development.
     *
     * @return int Returns 0 on success.
     */
    public function handle(): int
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

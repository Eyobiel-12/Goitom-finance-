<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Expense;
use App\Models\User;

final class ExpenseCategorizationService
{
    /**
     * Auto-categorize expense based on vendor name
     */
    public function categorizeExpense(User $user, string $vendor, ?string $description = null): ?string
    {
        // Get vendor patterns from user's previous expenses
        $vendorPatterns = $this->getVendorPatterns($user);
        
        // Try exact vendor match first
        if (isset($vendorPatterns[$vendor])) {
            return $vendorPatterns[$vendor];
        }
        
        // Try partial matches
        foreach ($vendorPatterns as $pattern => $category) {
            if (str_contains(strtolower($vendor), strtolower($pattern))) {
                return $category;
            }
        }
        
        // Try description-based categorization
        if ($description) {
            return $this->categorizeByDescription($description);
        }
        
        return null;
    }

    /**
     * Build vendor patterns from user's expense history
     */
    private function getVendorPatterns(User $user): array
    {
        $expenses = Expense::where('user_id', $user->id)
            ->whereNotNull('vendor')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('vendor', 'category')
            ->get();

        $patterns = [];
        
        foreach ($expenses as $expense) {
            $vendor = strtolower($expense->vendor);
            $category = $expense->category;
            
            // Store exact vendor
            $patterns[$vendor] = $category;
            
            // Extract key words for partial matching
            $words = preg_split('/[\s\-_]+/', $vendor);
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $patterns[$word] = $category;
                }
            }
        }
        
        return $patterns;
    }

    /**
     * Categorize by description keywords
     */
    private function categorizeByDescription(string $description): ?string
    {
        $description = strtolower($description);
        
        $patterns = [
            'software' => ['software', 'app', 'subscription', 'saas', 'license'],
            'travel' => ['flight', 'hotel', 'taxi', 'uber', 'train', 'travel', 'trip'],
            'office' => ['office', 'stationery', 'supplies', 'equipment'],
            'marketing' => ['advertising', 'marketing', 'promotion', 'social media'],
            'utilities' => ['electricity', 'water', 'internet', 'phone', 'utility'],
            'meals' => ['restaurant', 'food', 'lunch', 'dinner', 'coffee'],
            'transport' => ['fuel', 'gas', 'parking', 'toll', 'car'],
        ];
        
        foreach ($patterns as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return ucfirst($category);
                }
            }
        }
        
        return null;
    }

    /**
     * Suggest category for new expense
     */
    public function suggestCategory(User $user, string $vendor, ?string $description = null): array
    {
        $suggestions = [];
        
        // Get most common categories for this vendor
        $vendorCategories = Expense::where('user_id', $user->id)
            ->where('vendor', 'like', "%{$vendor}%")
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(3)
            ->pluck('category')
            ->toArray();
        
        $suggestions = array_merge($suggestions, $vendorCategories);
        
        // Get most common categories overall
        $commonCategories = Expense::where('user_id', $user->id)
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('category')
            ->toArray();
        
        $suggestions = array_merge($suggestions, $commonCategories);
        
        return array_unique($suggestions);
    }
}

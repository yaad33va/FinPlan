<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Ownership rules for a category and everything below it (its budgets and their transactions).
 * Scoped route bindings guarantee that a nested budget/transaction belongs to the category in
 * the URL, so checking the category is enough for the whole hierarchy.
 *
 * - Owner: everything.
 * - Admin: may view and delete anyone's data (moderation), but not edit it or add to it.
 * - Other members: nothing (403).
 */
class CategoryPolicy
{
    /**
     * View the category, its budgets or its transactions.
     */
    public function view(User $user, Category $category): Response
    {
        return $this->owns($user, $category) || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You can only view your own categories, budgets and transactions.');
    }

    /**
     * Edit the category or create/edit its budgets and transactions.
     */
    public function update(User $user, Category $category): Response
    {
        return $this->owns($user, $category)
            ? Response::allow()
            : Response::deny('You can only modify your own categories, budgets and transactions.');
    }

    /**
     * Delete the category, one of its budgets or one of its transactions.
     */
    public function delete(User $user, Category $category): Response
    {
        return $this->owns($user, $category) || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You can only delete your own categories, budgets and transactions.');
    }

    protected function owns(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }
}

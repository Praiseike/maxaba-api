<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Mail\AdminWelcomeMail;

class AdminController extends Controller
{
    /**
     * Display a listing of admins
     */
    public function index(Request $request)
    {
        $query = Admin::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $admins = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email',
            'name' => 'string|max:255',
            'role' => 'string|max:50',
        ]);

        try {
            // Generate random password
            $randomPassword = $this->generateRandomPassword();
            
            // Create admin
            $admin = Admin::create([
                'email' => $request->email,
                'password' => Hash::make($randomPassword),
                'name' => $request->name,
                'role' => $request->role ?? 'admin',
                'email_verified_at' => now(), // Auto-verify admin emails
            ]);

            // Send welcome email with credentials
            Mail::to($admin->email)->send(new AdminWelcomeMail($admin, $randomPassword));

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully. Login credentials have been sent to their email.',
                'data' => $admin->makeHidden(['password', 'remember_token'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified admin
     */
    public function show($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $admin->makeHidden(['password', 'remember_token'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, $id)
    {
        try {
            $admin = Admin::findOrFail($id);

            $request->validate([
                'email' => [
                    'sometimes',
                    'email',
                    Rule::unique('admins', 'email')->ignore($admin->id)
                ],
                'name' => 'sometimes|string|max:255',
                'role' => 'sometimes|string|max:50',
                'is_active' => 'sometimes|boolean',
            ]);

            $admin->update($request->only([
                'email', 'name', 'role', 'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
                'data' => $admin->makeHidden(['password', 'remember_token'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified admin
     */
    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            // Prevent deleting self (if you have auth)
            if (auth('admin')->check() && auth('admin')->id() == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 400);
            }

            $admin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Admin deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend credentials to admin
     */
    public function resendCredentials($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            // Generate new password
            $newPassword = $this->generateRandomPassword();
            
            // Update password
            $admin->update([
                'password' => Hash::make($newPassword)
            ]);

            // Send email with new credentials
            Mail::to($admin->email)->send(new AdminWelcomeMail($admin, $newPassword, true));

            return response()->json([
                'success' => true,
                'message' => 'New credentials have been sent to the admin\'s email'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle admin status (active/inactive)
     */
    public function toggleStatus($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            
            $admin->update([
                'is_active' => !($admin->is_active ?? true)
            ]);

            $status = $admin->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Admin {$status} successfully",
                'data' => $admin->makeHidden(['password', 'remember_token'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle admin status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Ensure password has at least one uppercase, lowercase, number, and symbol
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
            return $this->generateRandomPassword($length); // Regenerate if requirements not met
        }
        
        return $password;
    }

    /**
     * Bulk actions for admins
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admins,id'
        ]);

        try {
            $adminIds = $request->admin_ids;
            $action = $request->action;

            // Prevent bulk action on self
            if (auth('admin')->check() && in_array(auth('admin')->id(), $adminIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot perform bulk actions on your own account'
                ], 400);
            }

            switch ($action) {
                case 'delete':
                    Admin::whereIn('id', $adminIds)->delete();
                    $message = 'Selected admins deleted successfully';
                    break;
                
                case 'activate':
                    Admin::whereIn('id', $adminIds)->update(['is_active' => true]);
                    $message = 'Selected admins activated successfully';
                    break;
                
                case 'deactivate':
                    Admin::whereIn('id', $adminIds)->update(['is_active' => false]);
                    $message = 'Selected admins deactivated successfully';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }
}
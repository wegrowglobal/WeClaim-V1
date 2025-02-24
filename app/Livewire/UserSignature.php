<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserSignature extends Component
{
    use WithFileUploads;

    public $signature;
    public $signatureImage;
    public $user;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        $this->user = Auth::user();
        $this->signatureImage = $this->user->signature_path;
    }

    public function updatedSignature()
    {
        $this->validate([
            'signature' => 'required|image', // 1MB Max
        ]);

        try {
            // First, delete the old signature file if it exists
            if ($this->user->signature_path) {
                $oldPath = $this->user->signature_path;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Generate a unique filename with timestamp
            $extension = $this->signature->getClientOriginalExtension();
            $filename = 'signatures/' . time() . '_' . Str::random(20) . '.' . $extension;
            
            // Store the new signature
            $path = $this->signature->storeAs('public', $filename);
            
            if (!$path) {
                throw new \Exception('Failed to store the signature file.');
            }

            // Update user record with fresh instance
            $user = User::find($this->user->id);
            $updated = $user->update([
                'signature_path' => $filename
            ]);

            if (!$updated) {
                throw new \Exception('Failed to update user record.');
            }

            // Refresh the component data
            $this->user = $user->fresh();
            $this->signatureImage = $filename;
            $this->signature = null;

            Log::info('Signature updated successfully', [
                'user_id' => $this->user->id,
                'old_path' => $oldPath ?? null,
                'new_path' => $filename,
                'storage_path' => $path
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Signature uploaded successfully!'
            ]);

            // Force a component refresh
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            Log::error('Failed to upload signature', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to upload signature. Please try again.'
            ]);
        }
    }

    public function deleteSignature()
    {
        if ($this->user->signature_path) {
            $path = $this->user->signature_path;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            // Update user with fresh instance
            $user = User::find($this->user->id);
            $user->update([
                'signature_path' => null
            ]);

            // Refresh the component data
            $this->user = $user->fresh();
            $this->signatureImage = null;
            
            Log::info('Signature deleted successfully', [
                'user_id' => $this->user->id,
                'path' => $path
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Signature deleted successfully!'
            ]);

            // Force a component refresh
            $this->dispatch('refreshComponent');
        }
    }

    public function render()
    {
        // Ensure we have the latest user data
        $this->user = User::find($this->user->id);
        $this->signatureImage = $this->user->signature_path;
        
        return view('livewire.user-signature');
    }
}

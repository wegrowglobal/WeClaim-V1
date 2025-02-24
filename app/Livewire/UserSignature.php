<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserSignature extends Component
{
    use WithFileUploads;

    public $signature;
    public $signatureImage;
    public $drawingData;
    public $user;
    public $showDrawingPad = false;

    public function mount()
    {
        $this->user = auth()->user();
        $this->signatureImage = $this->user->signature_path;
    }

    public function getSignatureUrlProperty()
    {
        if (!$this->signatureImage) {
            return null;
        }

        $fileExists = file_exists(public_path('storage/' . $this->signatureImage));
        $imagePath = $fileExists ? asset('storage/' . $this->signatureImage) : null;

        Log::info('Signature URL generated', [
            'user_id' => $this->user->id,
            'signature_path' => $this->signatureImage,
            'file_exists' => $fileExists,
            'image_path' => $imagePath,
        ]);

        return $imagePath;
    }

    public function toggleDrawingPad()
    {
        $this->showDrawingPad = !$this->showDrawingPad;
    }

    public function updatedSignature()
    {
        $this->validate([
            'signature' => 'image|max:1024', // 1MB Max
        ]);

        try {
            $path = $this->signature->store('signatures', 'public');
            
            if ($this->user->signature_path) {
                Storage::disk('public')->delete($this->user->signature_path);
            }

            $this->user->update([
                'signature_path' => $path
            ]);

            $this->signatureImage = $path;
            $this->signature = null;

            $this->dispatch('signature-updated', [
                'signature_path' => $path
            ]);

            Log::info('Signature updated', [
                'user_id' => $this->user->id,
                'path' => $path
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Signature uploaded successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload signature', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to upload signature. Please try again.'
            ]);
        }
    }

    public function saveDrawnSignature()
    {
        $this->validate([
            'drawingData' => 'required|string|min:10',
        ]);

        // Remove the data URL prefix to get the base64 string
        $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $this->drawingData);
        
        // Decode base64 to binary
        $imageData = base64_decode($base64Image);
        
        // Generate a unique filename
        $filename = 'signatures/' . Str::random(40) . '.png';
        
        try {
            // Store the image
            Storage::disk('public')->put($filename, $imageData);

            // Delete old signature if exists
            if ($this->user->signature_path) {
                Storage::disk('public')->delete($this->user->signature_path);
            }

            // Update user's signature path
            $this->user->update([
                'signature_path' => $filename
            ]);

            $this->signatureImage = $filename;
            $this->showDrawingPad = false;
            $this->drawingData = null;

            $this->dispatch('signature-updated', [
                'signature_path' => $filename
            ]);

            Log::info('Drawn signature updated', [
                'user_id' => $this->user->id,
                'path' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save drawn signature', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to save signature. Please try again.'
            ]);
        }
    }

    public function deleteSignature()
    {
        if ($this->user->signature_path) {
            Storage::disk('public')->delete($this->user->signature_path);
            
            $this->user->update([
                'signature_path' => null
            ]);

            $this->signatureImage = null;
            
            $this->dispatch('signature-updated', [
                'signature_path' => null
            ]);

            Log::info('Signature deleted', [
                'user_id' => $this->user->id
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Signature deleted successfully!'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.user-signature');
    }
}

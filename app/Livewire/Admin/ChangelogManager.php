<?php

namespace App\Livewire\Admin;

use App\Models\System\Changelog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ChangelogManager extends Component
{
    use WithPagination;

    public $title = '';
    public $content = '';
    public $version = '';
    public $type = 'feature';
    public $is_published = false;
    
    public $editingChangelogId = null;
    public $isEditing = false;
    public $confirmingDeletion = false;
    public $deleteId = null;
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'version' => 'nullable|string|max:50',
        'type' => 'required|in:feature,improvement,bugfix,other',
        'is_published' => 'boolean',
    ];

    public function render()
    {
        // Check if user is super admin
        if (Auth::user()->role_id !== 5) {
            return view('livewire.error', [
                'message' => 'Unauthorized access.'
            ]);
        }

        $changelogs = Changelog::orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.changelog-manager', [
            'changelogs' => $changelogs
        ]);
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'nullable|string|max:20',
            'type' => 'required|in:feature,improvement,bugfix,other',
        ]);

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'version' => $this->version,
            'type' => $this->type,
            'is_published' => $this->is_published,
            'created_by' => Auth::id(),
        ];

        if ($this->is_published && !$this->isEditing) {
            $data['published_at'] = now();
        }

        try {
            if ($this->isEditing) {
                $changelog = Changelog::findOrFail($this->editingChangelogId);
                
                // If we're publishing for the first time
                if ($this->is_published && !$changelog->is_published) {
                    $data['published_at'] = now();
                }
                
                $changelog->update($data);
                $this->dispatch('notify', message: 'Changelog updated successfully!', type: 'success');
            } else {
                Changelog::create($data);
                $this->dispatch('notify', message: 'Changelog created successfully!', type: 'success');
            }

            $this->resetForm();
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $this->editingChangelogId = $id;
        
        $changelog = Changelog::findOrFail($id);
        
        $this->title = $changelog->title;
        $this->content = $changelog->content;
        $this->version = $changelog->version;
        $this->type = $changelog->type;
        $this->is_published = $changelog->is_published;
        
        // Dispatch event to update TinyMCE with content
        $this->dispatch('contentUpdated', content: $this->content);
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeletion = true;
        $this->deleteId = $id;
    }

    public function delete()
    {
        try {
            $changelog = Changelog::findOrFail($this->deleteId);
            $changelog->delete();
            
            $this->confirmingDeletion = false;
            $this->deleteId = null;
            
            $this->dispatch('notify', message: 'Changelog deleted successfully!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error deleting changelog: ' . $e->getMessage(), type: 'error');
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->deleteId = null;
    }

    public function resetForm()
    {
        $this->reset(['title', 'content', 'version', 'type', 'is_published', 'isEditing', 'editingChangelogId']);
        $this->resetValidation();
        $this->dispatch('formReset');
    }

    public function togglePublish($id)
    {
        try {
            $changelog = Changelog::findOrFail($id);
            $wasPublished = $changelog->is_published;
            
            $changelog->is_published = !$wasPublished;
            
            // If we're publishing for the first time
            if (!$wasPublished && $changelog->is_published) {
                $changelog->published_at = now();
            }
            
            $changelog->save();
            
            $status = $changelog->is_published ? 'published' : 'unpublished';
            $this->dispatch('notify', message: "Changelog {$status} successfully!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }
}

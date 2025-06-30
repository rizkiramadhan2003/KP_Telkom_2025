<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HdDaman;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class HdDamanManager extends Component
{
    public $name;
    public $date_of_birth;
    public $hdDamanId;
    public $isEditing = false;

    protected $rules = [
        'name' => 'required|string|max:255|unique:hd_damans,name',
        'date_of_birth' => 'required|date',
    ];

    public function render()
    {
        return view('livewire.hd-daman-manager', [
            'hdDamans' => HdDaman::all(),
        ]);
    }

    public function create()
    {
        $this->validate();

        // Generate email and password
        $email = strtolower(str_replace(' ', '', $this->name)) . '@tif.com';
        $password = Carbon::parse($this->date_of_birth)->format('ymd');

        // Create user
        $user = User::create([
            'name' => $this->name,
            'email' => $email,
            'password' => Hash::make($password),
            'date_of_birth' => $this->date_of_birth,
        ]);

        // Assign hd-daman role
        $role = Role::where('name', 'hd-daman')->first();
        if ($role) {
            $user->assignRole($role);
        }

        // Create HdDaman and link to user
        HdDaman::create([
            'name' => $this->name,
            'user_id' => $user->id,
        ]);

        $this->reset(['name', 'date_of_birth']);
        session()->flash('message', 'HdDaman and user created successfully.');
    }

    public function edit($id)
    {
        $hdDaman = HdDaman::findOrFail($id);
        $this->name = $hdDaman->name;
        $this->hdDamanId = $hdDaman->id;
        $this->isEditing = true;

        // Load user's date of birth if available
        if ($hdDaman->user) {
            $this->date_of_birth = Carbon::parse($hdDaman->user->date_of_birth)->format('Y-m-d');
        }
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:hd_damans,name,' . $this->hdDamanId,
            'date_of_birth' => 'required|date',
        ]);

        $hdDaman = HdDaman::findOrFail($this->hdDamanId);
        $hdDaman->update(['name' => $this->name]);

        // Update user details
        if ($hdDaman->user) {
            $hdDaman->user->update([
                'name' => $this->name,
                'email' => strtolower(str_replace(' ', '', $this->name)) . '@tif.com',
                'password' => Hash::make(Carbon::parse($this->date_of_birth)->format('ymd')),
                'date_of_birth' => $this->date_of_birth,
            ]);
        }

        $this->reset(['name', 'date_of_birth', 'hdDamanId', 'isEditing']);
        session()->flash('message', 'HdDaman and user updated successfully.');
    }

    public function delete($id)
    {
        $hdDaman = HdDaman::findOrFail($id);
        if ($hdDaman->user) {
            $hdDaman->user->delete();
        }
        $hdDaman->delete();
        session()->flash('message', 'HdDaman and associated user deleted successfully.');
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'date_of_birth', 'hdDamanId', 'isEditing']);
    }
}
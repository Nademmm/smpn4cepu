<?php

namespace App\Livewire\Staff;

use App\Enums\EmploymentStatus;
use App\Models\StaffMember;
use Livewire\Component;

class StaffDirectory extends Component
{
    public string $search = '';
    public string $statusFilter = '';
    public string $positionFilter = '';

    public function render()
    {
        $query = StaffMember::where('is_active', true);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('nip', 'like', '%' . $this->search . '%')
                  ->orWhere('position', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('employment_status', $this->statusFilter);
        }

        if (!empty($this->positionFilter)) {
            $query->where('position', 'like', '%' . $this->positionFilter . '%');
        }

        $staffMembers = $query->orderBy('display_order')->get();

        $statuses = [
            EmploymentStatus::PNS->value => 'PNS',
            EmploymentStatus::PPPK->value => 'PPPK',
            EmploymentStatus::HONORER->value => 'Honorer',
        ];

        return view('livewire.staff.staff-directory', [
            'staffMembers' => $staffMembers,
            'statuses' => $statuses,
        ]);
    }
}

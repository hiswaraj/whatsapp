<?php

namespace App\Exports;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch contacts collection for the logged-in user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Contact::where('user_id', Auth::id())
            ->where('is_temporary', false)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Define the heading row.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'name',
            'mobile_number',
            'email',
            'tags',
            'notes'
        ];
    }

    /**
     * Map each contact record to rows.
     *
     * @param mixed $contact
     * @return array
     */
    public function map($contact): array
    {
        $tagsRaw = is_array($contact->tags) ? implode(', ', $contact->tags) : '';

        return [
            $contact->name,
            $contact->mobile_number,
            $contact->email,
            $tagsRaw,
            $contact->notes
        ];
    }
}

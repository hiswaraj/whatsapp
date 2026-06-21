<?php

namespace App\Imports;

use App\Models\Contact;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped = 0;

    /**
     * Parse and import the contacts collection.
     *
     * @param Collection $rows
     * @throws Exception
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $firstRow = $rows->first()->toArray();
        $hasName = array_key_exists('name', $firstRow);
        $hasMobile = array_key_exists('mobile_number', $firstRow) || array_key_exists('mobile', $firstRow);

        if (!$hasName || !$hasMobile) {
            throw new Exception('Invalid spreadsheet structure. Make sure "name" and "mobile_number" headers are present.');
        }

        $userId = Auth::id();

        foreach ($rows as $row) {
            $name = isset($row['name']) ? trim((string)$row['name']) : '';
            
            $mobileNumber = '';
            if (isset($row['mobile_number'])) {
                $mobileNumber = trim((string)$row['mobile_number']);
            } elseif (isset($row['mobile'])) {
                $mobileNumber = trim((string)$row['mobile']);
            }

            if (empty($name) || empty($mobileNumber)) {
                continue;
            }

            // Normalize mobile number: keep digits and +
            $mobileNumber = preg_replace('/[^0-9+]/', '', $mobileNumber);

            // Deduplicate per tenant
            $exists = Contact::where('user_id', $userId)
                ->where('mobile_number', $mobileNumber)
                ->exists();

            if ($exists) {
                $this->skipped++;
                continue;
            }

            $email = isset($row['email']) ? trim((string)$row['email']) : null;
            $tagsRaw = isset($row['tags']) ? trim((string)$row['tags']) : '';
            $notes = isset($row['notes']) ? trim((string)$row['notes']) : null;

            $tags = [];
            if (!empty($tagsRaw)) {
                $tags = array_map('trim', explode(',', $tagsRaw));
            }

            Contact::create([
                'user_id' => $userId,
                'name' => $name,
                'mobile_number' => $mobileNumber,
                'email' => empty($email) ? null : $email,
                'tags' => $tags,
                'notes' => empty($notes) ? null : $notes,
                'is_temporary' => false
            ]);

            $this->imported++;
        }
    }
}

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

            $normalizedNumber = Contact::normalizePhoneNumber($mobileNumber);

            // Deduplicate per tenant using flexible phone number matching
            $existing = Contact::findByMobile($userId, $mobileNumber);

            if ($existing) {
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
                'mobile_number' => $normalizedNumber,
                'email' => empty($email) ? null : $email,
                'tags' => $tags,
                'notes' => empty($notes) ? null : $notes,
                'is_temporary' => false
            ]);

            $this->imported++;
        }
    }
}

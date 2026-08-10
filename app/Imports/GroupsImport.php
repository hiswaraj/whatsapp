<?php

namespace App\Imports;

use App\Models\Contact;
use App\Models\ContactGroup;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GroupsImport implements ToCollection, WithHeadingRow
{
    public int $groupsCreated = 0;
    public int $groupsProcessed = 0;
    public int $contactsImported = 0;

    /**
     * Parse and import the group collection.
     *
     * @param Collection $rows
     * @throws Exception
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $userId = Auth::id();

        foreach ($rows as $row) {
            $groupName = '';
            if (isset($row['group_name'])) {
                $groupName = trim((string)$row['group_name']);
            } elseif (isset($row['group'])) {
                $groupName = trim((string)$row['group']);
            } elseif (isset($row['name']) && !isset($row['mobile_number']) && !isset($row['mobile']) && !isset($row['phone'])) {
                $groupName = trim((string)$row['name']);
            }

            if (empty($groupName)) {
                continue;
            }

            // Find or create the group for this user
            $group = ContactGroup::where('user_id', $userId)
                ->where('name', $groupName)
                ->first();

            if (!$group) {
                $group = ContactGroup::create([
                    'user_id' => $userId,
                    'name' => $groupName
                ]);
                $this->groupsCreated++;
            }
            $this->groupsProcessed++;

            // Extract contact information if provided in the row
            $contactName = '';
            if (isset($row['contact_name'])) {
                $contactName = trim((string)$row['contact_name']);
            } elseif (isset($row['name']) && (isset($row['mobile_number']) || isset($row['mobile']) || isset($row['phone']))) {
                $contactName = trim((string)$row['name']);
            }

            $mobileNumber = '';
            if (isset($row['mobile_number'])) {
                $mobileNumber = trim((string)$row['mobile_number']);
            } elseif (isset($row['mobile'])) {
                $mobileNumber = trim((string)$row['mobile']);
            } elseif (isset($row['phone'])) {
                $mobileNumber = trim((string)$row['phone']);
            }

            if (!empty($mobileNumber)) {
                $normalizedNumber = Contact::normalizePhoneNumber($mobileNumber);
                $existing = Contact::findByMobile($userId, $mobileNumber);

                if ($existing) {
                    $existing->groups()->syncWithoutDetaching([$group->id]);
                    $this->contactsImported++;
                } else {
                    $email = isset($row['email']) ? trim((string)$row['email']) : null;
                    $tagsRaw = isset($row['tags']) ? trim((string)$row['tags']) : '';
                    $notes = isset($row['notes']) ? trim((string)$row['notes']) : null;

                    $tags = [];
                    if (!empty($tagsRaw)) {
                        $tags = array_map('trim', explode(',', $tagsRaw));
                    }

                    $contact = Contact::create([
                        'user_id' => $userId,
                        'name' => !empty($contactName) ? $contactName : 'Contact (' . $normalizedNumber . ')',
                        'mobile_number' => $normalizedNumber,
                        'email' => empty($email) ? null : $email,
                        'tags' => $tags,
                        'notes' => empty($notes) ? null : $notes,
                        'is_temporary' => false
                    ]);

                    $contact->groups()->syncWithoutDetaching([$group->id]);
                    $this->contactsImported++;
                }
            }
        }
    }
}

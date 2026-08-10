<?php

namespace App\Imports;

use App\Models\Contact;
use App\Models\ContactGroup;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped = 0;
    protected ?int $defaultGroupId = null;

    public function __construct(?int $defaultGroupId = null)
    {
        $this->defaultGroupId = $defaultGroupId;
    }

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

            // Handle Group(s) from Excel row
            $groupIds = [];
            if (!empty($this->defaultGroupId)) {
                $groupIds[] = $this->defaultGroupId;
            }

            $rawGroupName = '';
            foreach (['group_name', 'group', 'groups', 'contact_group', 'contact_groups', 'group_id'] as $k) {
                if (isset($row[$k]) && !empty(trim((string)$row[$k]))) {
                    $rawGroupName = trim((string)$row[$k]);
                    break;
                }
            }

            if (!empty($rawGroupName)) {
                // Split comma-separated group names if any
                $groupNames = array_filter(array_map('trim', explode(',', $rawGroupName)));
                foreach ($groupNames as $gName) {
                    if (!empty($gName)) {
                        $group = null;
                        if (is_numeric($gName)) {
                            $group = ContactGroup::where('user_id', $userId)->where('id', (int)$gName)->first();
                        }
                        if (!$group) {
                            $group = ContactGroup::where('user_id', $userId)
                                ->whereRaw('LOWER(name) = ?', [strtolower($gName)])
                                ->first();
                        }

                        if (!$group) {
                            $group = ContactGroup::create([
                                'user_id' => $userId,
                                'name' => $gName
                            ]);
                        }
                        if ($group && !in_array($group->id, $groupIds)) {
                            $groupIds[] = $group->id;
                        }
                    }
                }
            }

            // Deduplicate per tenant using flexible phone number matching
            $existing = Contact::findByMobile($userId, $mobileNumber);

            if ($existing) {
                if (!empty($groupIds)) {
                    $existing->groups()->syncWithoutDetaching($groupIds);
                }
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

            $contact = Contact::create([
                'user_id' => $userId,
                'name' => $name,
                'mobile_number' => $normalizedNumber,
                'email' => empty($email) ? null : $email,
                'tags' => $tags,
                'notes' => empty($notes) ? null : $notes,
                'is_temporary' => false
            ]);

            if (!empty($groupIds)) {
                $contact->groups()->syncWithoutDetaching($groupIds);
            }

            $this->imported++;
        }
    }
}

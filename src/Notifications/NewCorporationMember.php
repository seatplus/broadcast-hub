<?php

namespace Seatplus\BroadcastHub\Notifications;

use Illuminate\Support\Facades\Validator;
use Seatplus\Eveapi\Models\Corporation\CorporationInfo;
use Seatplus\Eveapi\Models\Corporation\CorporationMemberTracking;

abstract class NewCorporationMember extends Notification
{
    protected static ?string $title = 'New Corporation Member';

    protected static ?string $description = 'Notify when a new corporation member joins';

    protected static array $permissions = ['view member tracking'];

    protected static ?array $corporation_roles = ['director'];

    protected static string $identifier = 'corporation_id';

    protected static string $model = CorporationMemberTracking::class;

    protected static string $entity_type = CorporationInfo::class;

    protected array $new_corporation_members = [];

    /**
     * @throws \Exception
     */
    public function __construct(
        array $new_corporation_members,
        protected CorporationInfo $corporation,
    ) {

        $this->validateCollection($new_corporation_members);

    }

    /**
     * @throws \Exception
     */
    private function validateCollection(array $new_corporation_members): void
    {

        $validator = Validator::make($new_corporation_members, [
            '*.start_date' => 'required|date',
            '*.character' => 'required|array',
            '*.character.character_id' => 'required|integer',
            '*.character.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $this->new_corporation_members = $validator->validated();

    }
}

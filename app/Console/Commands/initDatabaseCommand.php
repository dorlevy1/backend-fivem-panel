<?php

namespace App\Console\Commands;

use App\Models\ActionPermission;
use App\Models\DiscordBot;
use App\Models\PermissionType;
use App\Models\Settings;
use Illuminate\Console\Command;

class initDatabaseCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settingsData = [
            'Translations' => [
                'excerpt' => 'This information will be displayed publicly so be careful what you share.',
                'data' => [
                    [
                        'label' => 'Top Banner',
                        'value' => 'Top Banner'
                    ],
                    [
                        'label' => 'Online Players',
                        'value' => 'Top Banner'
                    ]
                ]
            ],
            'Permissions' => [
                'excerpt' => 'This information will be displayed publicly so be careful what you share.',
                'data' => [
                    [
                        'label' => 'Players',
                        'value' => ''
                    ],
                    [
                        'label' => 'Bans',
                        'value' => ''
                    ],
                    [
                        'label' => 'Warns',
                        'value' => ''
                    ],
                    [
                        'label' => 'Gangs',
                        'value' => ''
                    ],
                    [
                        'label' => 'Redeem Code Requests',
                        'value' => ''
                    ],
                    [
                        'label' => 'Gang Requests',
                        'value' => ''
                    ]
                ]
            ],
            'Environments' => [
                'excerpt' => 'This information will be displayed publicly so be careful what you share.',
                'data' => [
                    ['label' => 'Fivem IP', 'value' => ''],
                    ['label' => 'Fivem Key', 'value' => '']
                ]
            ]
        ];

        foreach ($settingsData as $key => $data) {
            foreach ($data['data'] as $d) {
                Settings::updateOrCreate(
                    [
                        'category' => $key,
                        'label' => $d['label']
                    ],
                    [
                        'value' => $d['value'],
                    ]
                );
            }
        }

        $permissionActions = ['VIEW', 'UPDATE', 'REMOVE', 'CREATE'];

        foreach ($permissionActions as $type) {
            ActionPermission::updateOrCreate([
                'name' => $type
            ], [
                'name' => $type
            ]);
        }

        $permissionTypes = ['Admin', 'Senior Admin', 'Head Admin', 'Staff Manager', 'Management', 'God', 'Dev'];

        foreach ($permissionTypes as $type) {
            PermissionType::updateOrCreate([
                'name' => $type
            ], [
                'name' => $type
            ]);
        }


        $instructions = [
            [
                'name' => 'Gang Member Registration Form',
                'value' => 'Add a minimum of 10 and a maximum of 15 names of gang members. For each, write the full name, age, and Discord ID. Specify for each whether they are currently in the gang on the server or not, and whether they have a whitelist or not. Bosses and co-bosses over 16 are mandatory!'
            ],
            [
                'name' => 'CID Submission Requirement',
                'value' => 'Please specify the CID of each player in this field. Without this, you will not receive access in the game, and tickets on the subject will not receive a response if the CID is not entered here.'
            ],
            [
                'name' => 'Gang Selection and Customization',
                'value' => 'Choose from the available options the gang you desire. The chosen gang includes pre-defined color, neighborhood, status, etc., and the options before you are the currently available ones. For questions or extreme cases, please open a support ticket.'
            ],
            [
                'name' => 'Approval Notification for Crime Server Access',
                'value' => 'If approved, you will be notified and receive invitations to the crime server, along with corresponding roles. If not approved, you will also be informed.'
            ]
        ];

        $addYourGang = [
            [
                'name' => 'Command Usage',
                'value' => 'Please use the following command to register gang members:'
            ],
            [
                'name' => 'Command Syntax',
                'value' => '/gangmembers [boss_id] [co_boss_id] [member-1] [member-2] ... [member-10]'
            ],
            [
                'name' => 'Replace [boss]',
                'value' => 'Replace [boss] with the Discord Tag of the gang boss.'
            ],
            [
                'name' => 'Replace [co_boss]',
                'value' => 'Replace [co_boss] with the Discord Tag of the co-boss.'
            ],
            [
                'name' => 'Replace [member-1] to [member-10]',
                'value' => 'Replace [member-1] to [member-10] with the Discord Tags of the remaining gang members. Include at least 10 Tags and up to a maximum of 15 Tags.'
            ],
            ['name' => 'Separation', 'value' => 'Ensure each ID is separated by a space.'],
            [
                'name' => 'Execution',
                'value' => 'Once all IDs are correctly entered, execute the command to register the gang members.'
            ]
        ];

        $discord = [
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Client ID', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Client Secret', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Bot Token', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Scopes', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Main Guild', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Logs Guild', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Auth', 'label' => 'Redirect URI', 'value' => 'None'],
            ['excerpt' => '', 'category' => 'Gang Area', 'label' => 'Title Start Request', 'value' => 'None'],
            [
                'category' => 'Gang Area',
                'label' => 'Instructions',
                'excerpt' => 'Add your instruction how they should done the request.',
                'value' => json_encode($instructions)
            ],
            [
                'category' => 'Gang Area',
                'label' => 'Member Instructions',
                'excerpt' => 'Add your instruction how they should add the members.',
                'value' => json_encode($addYourGang)
            ],
        ];

        foreach ($discord as $data) {

            DiscordBot::updateOrCreate(
                [
                    'label' => $data['label'],
                    'category' => $data['category'],
                ],
                [
                    'value' => $data['value'],
                    'excerpt' => $data['excerpt'],
                ]
            );
        }
    }
}

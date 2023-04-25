<?php

namespace App\Console\Commands;

use App\Parser\CSVFileParser;
use App\Rules\RuleSet;
use Illuminate\Console\Command;

class CheckClaimability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flights:claimability:check {csv : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if flight in CSV file are claimable regarding rule set';

    protected $ruleSet = [
        'field' => 'country',
        'map' => [CheckClaimability::class, 'isEU'],
        'results' => [
            '1' => [
                'status' => [
                    'Cancel' => [
                        'value' => '<=14',
                    ],
                    'Delay' => [
                        'value' => [
                            '<3' => false,
                            '>=3' => true,
                        ],
                    ],
                ],
            ],
            '0' => false,
        ]
    ];


    /** @var CSVFileParser */
    private $parser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CSVFileParser $parser)
    {
        parent::__construct();

        $this->parser = $parser;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filePath = $this->argument('csv');
        $data = $this->parser->parse($filePath, ['country', 'status', 'value']);

        $ruleSet = RuleSet::fromArray($this->ruleSet);

        // $rs = new RuleSet('country', [
        //     '0' => false,
        //     '1' => new RuleSet('status', [
        //         'Cancel' => new RuleSet('value', '<=14'),
        //         'Delay' => new RuleSet('value', '>=3'),
        //     ]),
        // ], [self::class, 'isEU']);

        foreach ($data as $row) {
            echo \sprintf(
                '%s %s %s %s',
                $row['country'],
                $row['status'],
                $row['value'],
                // self::isEU($row['country']) && (
                //      ($row['status'] == 'Cancel' && $row['value'] <= 14) ||
                //      ($row['status']] == 'Delay' && $row['value] >= 3)
                // ) ? 'Y' : 'N'
                $ruleSet->getResult($row) ? 'Y' : 'N'
            );
            // echo $rs->getResult($row) ? 'Y' : 'N';
            echo \PHP_EOL;
        }
    }

    public static function isEU($country)
    {
        // dumm. or not..
        return $country != 'RU';
    }
}

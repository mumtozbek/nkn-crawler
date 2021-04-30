<?php

namespace App\Console\Commands;

use App\Models\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:state';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync uptime information.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $nodes = Node::all();

            foreach($nodes as $node) {
                $response = $this->getNodeState($node->host);

                if (is_string($response)) {
                    $json = json_decode($response);

                    if (!empty($json)) {
                        if (!empty($json->result)) {
                            $node->index($json);

                            continue;
                        } elseif (!empty($json->error)) {
                            if ($json->error->code == '-45022') {
                                $status = 'GENERATE_ID';
                            } elseif ($json->error->code == '-45024') {
                                $status = 'PRUNING_DB';
                            } else {
                                continue;
                            }

                            $node->update([
                                'status' => $status,
                            ]);

                            if ($status == $json->error->code) {
                                $node->uptimes()->create([
                                    'speed' => 0,
                                ]);
                            }

                            continue;
                        }
                    }

                    unset($json);
                }

                // Connection failed, so log it
                $node->update([
                    'status' => 'OFFLINE',
                ]);

                unset($response);
            }

            unset($nodes);
        } catch (\Exception $exception) {
            Log::alert($exception->getMessage());
        }
    }

    private function getNodeState($host)
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => '1',
            'method' => 'getnodestate',
            'params' => (object)[],
        ];

        $ch = curl_init('http://' . $host . ':30003/');

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]
        );

        return curl_exec($ch);
    }
}

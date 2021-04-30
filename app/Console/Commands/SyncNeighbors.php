<?php

namespace App\Console\Commands;

use App\Models\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncNeighbors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:neighbors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync neighbor nodes.';

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
            $config = json_decode(file_get_contents(storage_path('config.mainnet.json')));
            $seeds = $config->SeedList;
            rsort($seeds);

            foreach($seeds as $seed) {
                $addr = $this->extractHost($seed);
                $this->syncNeighbors($addr);
            };
        } catch (\Exception $exception) {
            Log::alert($exception->getMessage());
        }
    }

    public function syncNeighbors($host)
    {
        $response = $this->getNeighbors($host);

        if (is_string($response)) {
            $json = json_decode($response);

            if (!empty($json->result)) {
                foreach($json->result as $node) {
                    $addr = $this->extractHost($node->addr);

                    $childNode = Node::whereHost($addr);
                    if (!$childNode->exists()) {
                        Node::create([
                            'seed_id' => $node->id,
                            'host' => $addr,
                            'height' => $node->height,
                            'status' => $node->syncState,
                            'round_trip_time' => $node->roundTripTime,
                        ]);
                    } elseif (empty($childNode->height) || empty($childNode->status) || empty($childNode->round_trip_time)) {
                        $childNode->update([
                            'height' => $node->height,
                            'status' => $node->syncState,
                            'round_trip_time' => $node->roundTripTime,
                        ]);
                    }

                    $this->syncNeighbors($addr);

                    unset($addr);
                    unset($childNode);
                }
            }

            unset($json);
        }

        unset($response);
    }

    private function getNeighbors($host)
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => '1',
            'method' => 'getneighbor',
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

    private function extractHost($addr)
    {
        return trim(preg_replace('#^(\w+\:\/\/)(.*)(\:\d+)$#', '${2}', trim($addr)));
    }
}

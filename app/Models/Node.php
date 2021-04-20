<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    /**
     * Mass assignment fields.
     */
    protected $fillable = [
        'host',
        'status',
        'version',
        'country',
        'region',
        'city',
        'height',
        'proposals',
        'relays',
        'speed',
        'uptime',
        'round_trip_time',
        'seed_id',
        'response',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'host' => 'required|unique:nodes,host,' . $this->id,
            'account_id' => 'required|exists:accounts,id',
        ];
    }

    /**
     * Index a node.
     *
     * @param $json
     */
    public function index($json)
    {
        $result = $json->result;
        $speed = ($result->relayMessageCount / $result->uptime) * 3600;

        $this->update([
            'status' => $result->syncState,
            'version' => $result->version,
            'height' => $result->height,
            'proposals' => $result->proposalSubmitted,
            'relays' => $result->relayMessageCount,
            'uptime' => $result->uptime,
            'speed' => $speed,
            'response' => json_encode($result),
        ]);

        unset($result);
    }
}

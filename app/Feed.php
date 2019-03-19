<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $table = 'feeds';

    protected $fillable = ['url'];

    protected static $xml_node_properties = [
        'guid' => 'guid',
        'link' => 'link',
        'title' => 'title',
        'pubDate' => 'publish_date',
        'description' => 'description',
        'content' => 'content'
    ];


    public function updateFeedContent()
    {
        $rss = $this->loadRss();

        if (! empty($rss)) {
            foreach ($rss as $item) {
                if (! FeedContent::checkIfExists($item)) {
                    FeedContent::create(array_merge($item, ['feed_id' => $this->id]));
                }
            }
        }
    }

    protected function loadRss()
    {
        $data = [];

        try {
            $response = (new Client())->get($this->url);

            $xml = $response->getBody()->getContents();

            $xml = new \SimpleXMLElement($xml);

            if (property_exists($xml, "channel") && property_exists($xml->channel, "item")) {
                foreach ($xml->channel->item as $item) {
                    $data[] = $this->extractNodeProperties($item);
                }
            }
        }
        catch (\Exception $e) {

        }

        return $data;
    }

    protected function extractNodeProperties($node)
    {
        $result = [];

        foreach (static::$xml_node_properties as $prop => $modelKey) {
            if (property_exists($node, $prop)) {
                $result[$modelKey] = $prop == 'pubDate' ? Carbon::parse($node->{$prop})->toDateTimeString() : (string) $node->{$prop};
            }
        }

        return $result;
    }
}

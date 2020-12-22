<?php

namespace acme\hostingde;

use acme\common\Client;
use acme\common\Exception;
use Hostingde\API\DnsApi;
use Hostingde\API\Filter;
use Hostingde\API\Record;
use Hostingde\API\Zone;

// load hostingde require because its no real composer package

$reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
$vendorDir = dirname(dirname($reflection->getFileName()));
require $vendorDir . '/hostingde/api-php/require.php';

class Hostingde extends Client
{
    /**
     * Add challenge dns record
     *
     * @param string $fqdn
     * @param string $txt
     * @throws Exception
     */
    public function present(string $fqdn, string $txt)
    {
        $record = new Record();
        $record->set('name', $fqdn);
        $record->set('type', 'TXT');
        $record->set('content', $txt);
        $record->set('ttl', 60);

        $zone = $this->getZone($fqdn);
        $api = new DnsApi($this->config["apiKey"]);
        $api->zoneUpdate($zone->zoneConfig, [$record], [], []);

        if (count($api->getErrors())) {
            $error = $api->getErrors()[0];
            throw new Exception($error->text, $error->code);
        }
    }

    /**
     * Delete challenge dns record
     *
     * @param string $fqdn
     * @param string $txt
     * @throws Exception
     */
    public function cleanUp(string $fqdn, string $txt)
    {
        $zone = $this->getZone($fqdn);
        $records = $zone->records;
        $found = null;
        // txt records are saved with quotes, add these quotes to find record
        $txtZone = '"' . $txt . '"';
        foreach ($records as $record) {
            if ($record->name === $fqdn && $record->content === $txtZone) {
                $found = $record;
            }
        }
        if (!$found) {
            throw new Exception("Record " . $fqdn . " not found");
        }

        $api = new DnsApi($this->config["apiKey"]);
        $api->zoneUpdate($zone->zoneConfig, [], [], [$found]);
        if (count($api->getErrors())) {
            $error = $api->getErrors()[0];
            throw new Exception($error->text, $error->code);
        }
    }

    /**
     * Filter sub domains from challenge fqdn
     * and search for zone
     *
     * @param string $fqdn
     * @return Zone
     * @throws Exception
     */
    protected function getZone(string $fqdn): Zone
    {
        $splitZone = explode(".", $fqdn);
        $zoneName = implode(".", array_slice($splitZone, -2, 2));

        $api = new DnsApi($this->config["apiKey"]);
        $filter = new Filter();
        $filter->addFilter('ZoneName', $zoneName);

        $zones = $api->zonesFind($filter);
        if (!count($zones)) {
            throw new Exception("Zone not found");
        }

        return $zones[0];
    }
}
